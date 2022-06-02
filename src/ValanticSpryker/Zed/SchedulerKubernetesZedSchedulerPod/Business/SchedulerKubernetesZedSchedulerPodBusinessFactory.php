<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business;

use Maclof\Kubernetes\Client as KubernetesClient;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Twig\Environment;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApi;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\CreateExecutor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\DeleteExecutor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\DisableExecutor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\NullExecutor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\UpdateExecutor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessor;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessorInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyBuilder;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyBuilderInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator\CronFileTemplateGenerator;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator\CronFileTemplateGeneratorInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodDependencyProvider;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig getConfig()
 */
class SchedulerKubernetesZedSchedulerPodBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Maclof\Kubernetes\Client
     */
    public function getKubernetesClient(): KubernetesClient
    {
        return $this->getProvidedDependency(SchedulerKubernetesZedSchedulerPodDependencyProvider::CLIENT_KUBERNETES);
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwigEnvironment(): Environment
    {
        return $this->getProvidedDependency(SchedulerKubernetesZedSchedulerPodDependencyProvider::TWIG_ENVIRONMENT);
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesSetup(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createUpdateExecutor(),
                $this->createCreateExecutor(),
            ),
            $this->createKubernetesApi(),
            $this->getConfig(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesClean(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createDeleteExecutor(),
                $this->createNullExecutor(),
            ),
            $this->createKubernetesApi(),
            $this->getConfig(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesEnable(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createNullExecutor(),
                $this->createCreateExecutor(),
            ),
            $this->createKubernetesApi(),
            $this->getConfig(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesDisable(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createDisableExecutor(),
                $this->createNullExecutor(),
            ),
            $this->createKubernetesApi(),
            $this->getConfig(),
        );
    }

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface $executorForExistingJob
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface $executorForAbsentJob
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyBuilderInterface
     */
    public function createExecutionStrategyBuilder(
        ExecutorInterface $executorForExistingJob,
        ExecutorInterface $executorForAbsentJob
    ): ExecutionStrategyBuilderInterface {
        return new ExecutionStrategyBuilder(
            $this->createKubernetesApi(),
            $executorForExistingJob,
            $executorForAbsentJob,
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface
     */
    public function createCreateExecutor(): ExecutorInterface
    {
        return new CreateExecutor(
            $this->createKubernetesApi(),
            $this->createTemplateGenerator(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface
     */
    public function createUpdateExecutor(): ExecutorInterface
    {
        return new UpdateExecutor(
            $this->createKubernetesApi(),
            $this->createTemplateGenerator(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface
     */
    public function createDeleteExecutor(): ExecutorInterface
    {
        return new DeleteExecutor(
            $this->createKubernetesApi(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface
     */
    public function createDisableExecutor(): ExecutorInterface
    {
        return new DisableExecutor(
            $this->createKubernetesApi(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface
     */
    public function createNullExecutor(): ExecutorInterface
    {
        return new NullExecutor();
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface
     */
    private function createKubernetesApi(): KubernetesApiInterface
    {
        return new KubernetesApi($this->getKubernetesClient(), $this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator\CronFileTemplateGeneratorInterface
     */
    public function createTemplateGenerator(): CronFileTemplateGeneratorInterface
    {
        return new CronFileTemplateGenerator($this->getTwigEnvironment(), $this->getConfig());
    }
}
