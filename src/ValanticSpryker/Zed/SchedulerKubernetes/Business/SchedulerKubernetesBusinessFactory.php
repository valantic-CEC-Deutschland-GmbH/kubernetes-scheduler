<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business;

use Maclof\Kubernetes\Client as KubernetesClient;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Twig\Environment;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApi;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\CreateExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\DeleteExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\DisableExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\EnableExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\NullExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\UpdateExecutor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessor;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessorInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategyBuilder;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategyBuilderInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGenerator;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGeneratorInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesDependencyProvider;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig getConfig()
 */
class SchedulerKubernetesBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Maclof\Kubernetes\Client
     */
    public function getKubernetesClient(): KubernetesClient
    {
        return $this->getProvidedDependency(SchedulerKubernetesDependencyProvider::CLIENT_KUBERNETES);
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwigEnvironment(): Environment
    {
        return $this->getProvidedDependency(SchedulerKubernetesDependencyProvider::TWIG_ENVIRONMENT);
    }

    /**
     * @return array<\ValanticSpryker\Zed\SchedulerKubernetes\Dependency\Plugin\Hook\PreProcessSchedulerHookPluginInterface>
     */
    public function getPreProcessorPlugins(): array
    {
        return $this->getProvidedDependency(SchedulerKubernetesDependencyProvider::PREPROCESSOR_PLUGINS);
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesSetup(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createUpdateExecutor(),
                $this->createCreateExecutor(),
            ),
            $this->getPreProcessorPlugins(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesClean(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createDeleteExecutor(),
                $this->createNullExecutor(),
            ),
            $this->getPreProcessorPlugins(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesEnable(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createEnableExecutor(),
                $this->createCreateExecutor(),
            ),
            $this->getPreProcessorPlugins(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\ScheduleProcessorInterface
     */
    public function createSchedulerKubernetesDisable(): ScheduleProcessorInterface
    {
        return new ScheduleProcessor(
            $this->createExecutionStrategyBuilder(
                $this->createDisableExecutor(),
                $this->createNullExecutor(),
            ),
            $this->getPreProcessorPlugins(),
        );
    }

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForExistingJob
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForAbsentJob
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategyBuilderInterface
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
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createCreateExecutor(): ExecutorInterface
    {
        return new CreateExecutor(
            $this->createKubernetesApi(),
            $this->createTemplateGenerator(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createUpdateExecutor(): ExecutorInterface
    {
        return new UpdateExecutor(
            $this->createKubernetesApi(),
            $this->createTemplateGenerator(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createDeleteExecutor(): ExecutorInterface
    {
        return new DeleteExecutor(
            $this->createKubernetesApi(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createEnableExecutor(): ExecutorInterface
    {
        return new EnableExecutor(
            $this->createKubernetesApi(),
            $this->createTemplateGenerator(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createDisableExecutor(): ExecutorInterface
    {
        return new DisableExecutor(
            $this->createKubernetesApi(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function createNullExecutor(): ExecutorInterface
    {
        return new NullExecutor();
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface
     */
    private function createKubernetesApi(): KubernetesApiInterface
    {
        return new KubernetesApi($this->getKubernetesClient());
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGeneratorInterface
     */
    public function createTemplateGenerator(): KubernetesTemplateGeneratorInterface
    {
        return new KubernetesTemplateGenerator($this->getTwigEnvironment(), $this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\InconsistentJobRemoverInterface
     */
    public function createInconsistentJobsRemover(): InconsistentJobRemoverInterface
    {
        return new InconsistentJobRemover($this->createKubernetesApi());
    }
}
