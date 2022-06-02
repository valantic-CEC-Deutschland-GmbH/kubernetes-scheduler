<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Api;

use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use InvalidArgumentException;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\Collection;
use Maclof\Kubernetes\Exceptions\BadRequestException;
use Maclof\Kubernetes\Models\CronJob;
use Maclof\Kubernetes\Models\DeleteOptions;
use Maclof\Kubernetes\Models\Job;
use Maclof\Kubernetes\Models\Model;
use Maclof\Kubernetes\Repositories\Repository;
use Spryker\Shared\Log\LoggerTrait;
use ValanticSpryker\Shared\Log\LogExceptionTrait;

class KubernetesApi implements KubernetesApiInterface
{
    use LoggerTrait;
    use LogExceptionTrait;

    /**
     * @var string
     */
    private const PAYLOAD_KEY_JOBS = 'jobs';

    /**
     * @var string
     */
    private const PAYLOAD_KEY_NAME = 'name';

    /**
     * @var string
     */
    private const JSON_PATH_NAME = '.metadata.name';

    /**
     * @var string
     */
    private const JSON_PATH_DISABLED = '.spec.suspend';

    // Add a short period to graceful termination period (currently 30s)
    // Note we can also run into the case that a pod was just created and should be deleted already
    /**
     * @var int
     */
    private const MAX_DELETION_WAIT_TIME = 35;

    private Client $client;

    /**
     * @param \Maclof\Kubernetes\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getCronJobs(): SchedulerKubernetesResponseTransfer
    {
        try {
            $payload = $this->mapResourceNamesToArray(
                $this->client->cronJobs()->setLabelSelector(['spryker-resource' => 'cronjob'])->setFieldSelector([])->find(),
            );
            $this->getLogger()->debug('received CronJobs', $payload);
        } catch (BadRequestException $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($payload) ?: '');
    }

    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getJobs(): SchedulerKubernetesResponseTransfer
    {
        try {
            $payload = $this->mapResourceNamesToArray(
                $this->client->jobs()->setLabelSelector(['spryker-resource' => 'cronjob'])->setFieldSelector([])->find(),
            );
            $this->getLogger()->debug('received Jobs', $payload);
        } catch (BadRequestException $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($payload) ?: '');
    }

    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getAllJobs(): SchedulerKubernetesResponseTransfer
    {
        $cronJobsResult = $this->getCronJobs();
        $jobsResult = $this->getJobs();
        if ($cronJobsResult->getStatus() === false || $jobsResult->getStatus() === false) {
            return $this->createSchedulerKubernetesErrorResponseTransfer('CronJobs list or Jobs list is empty');
        }

        $jobList = array_merge_recursive(
            json_decode($cronJobsResult->getPayload(), true),
            json_decode($jobsResult->getPayload(), true),
        );

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($jobList));
    }

    /**
     * @param string $jobName
     * @param string $cronJobTemplate
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function createCronJob(string $jobName, string $cronJobTemplate): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('creating CronJob "%s"', $jobName), [$cronJobTemplate]);
        try {
            $response = $this->client->cronJobs()->create(new CronJob($cronJobTemplate, 'yaml'));
        } catch (BadRequestException | InvalidArgumentException $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }

        $this->getLogger()->info(sprintf('CronJob "%s" created', $jobName));

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($response) ?: '');
    }

    /**
     * @param string $jobName
     * @param string $jobTemplate
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function createJob(string $jobName, string $jobTemplate): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('creating Job "%s"', $jobName), [$jobTemplate]);
        try {
            $response = $this->client->jobs()->create(new Job($jobTemplate, 'yaml'));
        } catch (BadRequestException | InvalidArgumentException $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }

        $this->getLogger()->info(sprintf('Job "%s" created', $jobName));

        return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($response) ?: '');
    }

    /**
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function deleteCronJob(string $jobName): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('deleting CronJob "%s"', $jobName));

        if (!$this->deleteResources($this->client->cronJobs(), ['app.kubernetes.io/component' => $jobName])) {
            return $this->createSchedulerKubernetesErrorResponseTransfer('');
        }

        $this->getLogger()->info(sprintf('CronJob "%s" deleted', $jobName));

        return $this->createSchedulerKubernetesSuccessResponseTransfer('');
    }

    /**
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function deleteJob(string $jobName): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('deleting Job "%s"', $jobName));

        if (!$this->deleteResources($this->client->jobs(), ['app.kubernetes.io/component' => $jobName])) {
            return $this->createSchedulerKubernetesErrorResponseTransfer('');
        }

        $this->getLogger()->info(sprintf('Job "%s" deleted', $jobName));

        return $this->createSchedulerKubernetesSuccessResponseTransfer('');
    }

    /**
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function disableCronJob(string $jobName): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('disabling CronJob "%s"', $jobName));
        $job = $this->fetchCronJob($jobName);
        if ($job === null) {
            $this->getLogger()->notice(sprintf('CronJob "%s" does not exist anymore', $jobName));

            return $this->createSchedulerKubernetesErrorResponseTransfer(sprintf('CronJob %s does not exist', $jobName));
        }

        if (!$this->isCronJobEnabled($job)) {
            $this->getLogger()->info(sprintf('CronJob "%s" is already disabled', $jobName));

            return $this->createSchedulerKubernetesSuccessResponseTransfer('');
        }

        $patchedCronJob = new CronJob([
            'metadata' => ['name' => $jobName],
            'spec' => [
                'suspend' => true,
            ],
        ]);

        try {
            $response = $this->client->cronJobs()->patch($patchedCronJob);
            $this->getLogger()->info(sprintf('CronJob "%s" disabled', $jobName));

            return $this->createSchedulerKubernetesSuccessResponseTransfer(json_encode($response) ?: '');
        } catch (BadRequestException $exception) {
            $this->logError($exception);

            return $this->createSchedulerKubernetesErrorResponseTransfer($exception->getMessage());
        }
    }

    /**
     * @param string $payload
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    private function createSchedulerKubernetesSuccessResponseTransfer(string $payload): SchedulerKubernetesResponseTransfer
    {
        return (new SchedulerKubernetesResponseTransfer())
            ->setPayload($payload)
            ->setStatus(true);
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
     * @param \Maclof\Kubernetes\Collections\Collection $resources
     *
     * @return array<array>
     */
    private function mapResourceNamesToArray(Collection $resources): array
    {
        $payload = [self::PAYLOAD_KEY_JOBS => []];
        foreach ($resources as $resource) {
            /** @var \Maclof\Kubernetes\Models\CronJob|\Maclof\Kubernetes\Models\Job $resource */
            $payload[self::PAYLOAD_KEY_JOBS][] = [self::PAYLOAD_KEY_NAME => $resource->getJsonPath(self::JSON_PATH_NAME)[0]];
        }

        return $payload;
    }

    /**
     * @param string $jobName
     *
     * @return \Maclof\Kubernetes\Models\Model|null
     */
    private function fetchCronJob(string $jobName): ?Model
    {
        return $this->client->cronJobs()->setLabelSelector(['app.kubernetes.io/component' => $jobName])->first();
    }

    /**
     * @param \Maclof\Kubernetes\Models\Model $job
     *
     * @return bool
     */
    private function isCronJobEnabled(Model $job): bool
    {
        return $job->getJsonPath(self::JSON_PATH_DISABLED)->current() === false;
    }

    /**
     * @inheritDoc
     */
    public function stopExecution(string $jobName): SchedulerKubernetesResponseTransfer
    {
        $this->getLogger()->debug(sprintf('stopping execution of "%s"', $jobName));

        // remove pods
        $success = $this->deleteResources($this->client->pods(), ['app.kubernetes.io/component' => $jobName]);
        if (!$success) {
            $this->getLogger()->error(sprintf('Could not delete pods for "%s"', $jobName));

            return $this->createSchedulerKubernetesErrorResponseTransfer(sprintf('Resource deletion for "%s" failed', $jobName));
        }

        $this->getLogger()->info(sprintf('Removed pods for job "%s"', $jobName));

        return $this->createSchedulerKubernetesSuccessResponseTransfer('');
    }

    /**
     * @param \Maclof\Kubernetes\Repositories\Repository $repository
     * @param array $labelSelector
     * @param array $fieldSelector
     *
     * @return bool
     */
    private function deleteResources(Repository $repository, array $labelSelector = [], array $fieldSelector = []): bool
    {
        $resourceType = get_class($repository);
        $this->getLogger()->debug(sprintf(
            'deleting resources for repository "%s" with label "%s" and fields "%s"',
            $resourceType,
            var_export($labelSelector, true),
            var_export($fieldSelector, true),
        ));

        $resources = $repository->setLabelSelector($labelSelector)->setFieldSelector($fieldSelector)->find();
        if ($resources->count() === 0) {
            $this->getLogger()->notice(
                sprintf(
                    'No resources of type "%s" to delete',
                    $resourceType,
                ),
                ['labels' => $labelSelector, 'fields' => $fieldSelector],
            );

            return true;
        }

        foreach ($resources->toArray() as $resource) {
            /** @var \Maclof\Kubernetes\Models\Job $resource */
            $resourceName = $resource->getMetadata('name');

            $this->getLogger()->debug(sprintf('removing Kubernetes resource: %s', $resourceName));
            try {
                // use Background deletion explicitly because resources would be still visible with Foreground deletion
                // and we can't filter for this state
                $repository->delete($resource, new DeleteOptions(['propagationPolicy' => 'Background']));

                $this->getLogger()->info(sprintf('Resource "%s" of type "%s" deleted', $resourceName, $resourceType));
            } catch (BadRequestException $exception) {
                $this->logError($exception);
                $this->getLogger()->error(sprintf(
                    'could not remove Kubernetes resource "%s" of type "%s"',
                    $resourceName,
                    $resourceType,
                ));
            }
        }

        for ($i = 0; $i < static::MAX_DELETION_WAIT_TIME; $i++) {
            $resources = $repository->setLabelSelector($labelSelector)->setFieldSelector($fieldSelector)->find();
            if ($resources->count() === 0) {
                $this->getLogger()->info(
                    sprintf('All resources of type "%s" removed', $resourceType),
                    ['labels' => $labelSelector, 'fields' => $fieldSelector],
                );

                return true;
            }

            sleep(1);
        }

        $this->getLogger()->warning(sprintf(
            'Exceeded max deletion time of %d seconds, there may be still resources left!',
            static::MAX_DELETION_WAIT_TIME,
        ));

        return false;
    }
}
