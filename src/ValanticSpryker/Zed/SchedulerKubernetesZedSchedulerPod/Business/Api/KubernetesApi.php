<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api;

use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Models\ConfigMap;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig;

class KubernetesApi implements KubernetesApiInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    public const INDEX_NAME = 'name';

    /**
     * @var string
     */
    private const INDEX_DEFINITION = 'definition';

    /**
     * @var string
     */
    private const CRONTAB_FILENAME = 'root';

    /**
     * @var string
     */
    private const INDEX_DATA = 'data';

    /**
     * @var string
     */
    private const INDEX_METADATA = 'metadata';

    private Client $client;

    private SchedulerKubernetesZedSchedulerPodConfig $config;

    /**
     * @param \Maclof\Kubernetes\Client $client
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig $config
     */
    public function __construct(Client $client, SchedulerKubernetesZedSchedulerPodConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param string $payload
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    private function createSchedulerKubernetesSuccessResponseTransfer(string $payload, string $message = ''): SchedulerKubernetesResponseTransfer
    {
        return (new SchedulerKubernetesResponseTransfer())
            ->setPayload($payload)
            ->setStatus(true)
            ->setMessage($message);
    }

    /**
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    private function createSchedulerKubernetesErrorResponseTransfer(string $message): SchedulerKubernetesResponseTransfer
    {
        return (new SchedulerKubernetesResponseTransfer())
            ->setMessage($message)
            ->setStatus(false);
    }

    /**
     * @return \Maclof\Kubernetes\Models\ConfigMap
     */
    private function getConfigMap(): ConfigMap
    {
        return $this->client
            ->configMaps()
            ->setLabelSelector([])
            ->setFieldSelector(['metadata.name' => getenv('ZED_SCHEDULER_CM_NAME')])
            ->find()->first();
    }

    /**
     * @return array
     */
    private function getRawJobs(): array
    {
        $list = explode("\n", $this->getConfigMap()->getJsonPath('.data')->first()->getData()[self::CRONTAB_FILENAME]);
        foreach ($list as $index => $value) {
            // ignore comments
            if (strpos($value, '#name=') === false) {
                unset($list[$index]);
            }
        }

        return $list;
    }

    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getJobList(): SchedulerKubernetesResponseTransfer
    {
        $jobs = [];
        foreach ($this->getRawJobs() as $job) {
            $matches = [];
            if (preg_match('/#name=(.+)$/', $job, $matches) === 1) {
                $jobs[] = [self::INDEX_NAME => $matches[1], self::INDEX_DEFINITION => $job];
            }
        }
        $this->getLogger()->debug(sprintf('received job list: %s', var_export($jobs, true)));

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($jobs), 'Received job list');
    }

    /**
     * @inheritDoc
     */
    public function addToConfigMap(string $jobName, string $crontabFileContents): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('Adding job "%s" to ConfigMap', $jobName), ['payload' => $crontabFileContents]);

        try {
            $decodedData = $this->getRawJobs();
            $decodedData[] = $crontabFileContents;

            $patchModel = new ConfigMap([
                self::INDEX_METADATA => [self::INDEX_NAME => $this->getConfigMap()->getMetadata(self::INDEX_NAME)],
                self::INDEX_DATA => [
                    self::CRONTAB_FILENAME => implode("\n", $this->config->getCrontabPrefix() + $decodedData),
                ],
            ]);

            // only update data field, so use PATCH method
            $response = $this->client->configMaps()->patch($patchModel);

            $this->getLogger()->info(sprintf('Added job "%s" to crontab', $jobName));

            return $this->createSchedulerKubernetesSuccessResponseTransfer(
                json_encode($response),
                sprintf('Added job %s', $jobName),
            );
        } catch (Throwable $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function removeFromConfigMap(string $jobName): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('Removing job "%s" from ConfigMap', $jobName));

        try {
            $decodedData = $this->getRawJobs();

            foreach ($decodedData as $index => $crontabLine) {
                if (strpos($crontabLine, $jobName) !== false) {
                    unset($decodedData[$index]);
                }
            }

            $patchModel = new ConfigMap([
                self::INDEX_METADATA => [self::INDEX_NAME => $this->getConfigMap()->getMetadata(self::INDEX_NAME)],
                self::INDEX_DATA => [
                    self::CRONTAB_FILENAME => implode("\n", $this->config->getCrontabPrefix() + $decodedData),
                ],
            ]);

            // only update data field, so use PATCH method
            $response = $this->client->configMaps()->patch($patchModel);

            $this->getLogger()->info(sprintf('Removed job "%s" from crontab', $jobName));

            return $this->createSchedulerKubernetesSuccessResponseTransfer(
                json_encode($response),
                sprintf('Removed job %s', $jobName),
            );
        } catch (Throwable $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function restartScheduler(): SchedulerKubernetesResponseTransfer
    {
        try {
            $labelSelector = ['component' => getenv('ZED_SCHEDULER_POD_LABEL_COMPONENT')];
            $model = $this->client->pods()->setLabelSelector($labelSelector)->find();

            $deletedPods = [];
            foreach ($model->getIterator() as $pod) {
                /** @var \Maclof\Kubernetes\Models\Pod $pod */
                if ($pod->getJsonPath('.status.phase')[0] === 'Running') {
                    $deletedPods[] = $this->client->pods()->delete($pod);
                    $this->getLogger()->info(sprintf('Deleted pod "%s" to restart the scheduler', $pod->getMetadata(self::INDEX_NAME)));
                }
            }

            return $this->createSchedulerKubernetesSuccessResponseTransfer(
                json_encode($deletedPods),
                'Pod restart initiated',
            );
        } catch (Throwable $exception) {
            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }
    }
}
