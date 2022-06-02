<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes;

use Maclof\Kubernetes\Client;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Twig\Loader\FilesystemLoader;
use ValanticSpryker\Zed\SchedulerKubernetes\Communication\Plugin\Hook\InconsistentJobsRemovalHookPlugin;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig getConfig()
 */
class SchedulerKubernetesDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_KUBERNETES = 'CLIENT_KUBERNETES';

    /**
     * @var string
     */
    public const TWIG_ENVIRONMENT = 'TWIG_ENVIRONMENT';

    /**
     * @var string
     */
    public const PREPROCESSOR_PLUGINS = 'PREPROCESSOR_PLUGINS';

    /**
     * @uses \Spryker\Zed\Twig\Communication\Plugin\Application\TwigApplicationPlugin::SERVICE_TWIG
     *
     * @var string
     */
    public const SERVICE_TWIG = 'twig';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addKubernetesClient($container);
        $container = $this->addTwigEnvironment($container);
        $container = $this->addPreProcessorPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addKubernetesClient(Container $container): Container
    {
        $container->set(static::CLIENT_KUBERNETES, function (Container $container) {
            return new Client($this->getConfig()->getKubernetesConfig());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addTwigEnvironment(Container $container): Container
    {
        $container->set(static::TWIG_ENVIRONMENT, function (Container $container) {
            /** @var \Twig\Environment $twig */
            $twig = $container->getApplicationService(static::SERVICE_TWIG);
            $twig->setLoader($this->createFilesystemLoader());
            $twig->setCache(false);

            return $twig;
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addPreProcessorPlugins(Container $container): Container
    {
        $container->set(static::PREPROCESSOR_PLUGINS, function (Container $container) {
            return $this->getPreProcessorPlugins();
        });

        return $container;
    }

    /**
     * @return \Twig\Loader\FilesystemLoader
     */
    private function createFilesystemLoader(): FilesystemLoader
    {
        return new FilesystemLoader($this->getConfig()->getKubernetesTemplateFolders());
    }

    /**
     * @return array<\ValanticSpryker\Zed\SchedulerKubernetes\Communication\Plugin\Hook\InconsistentJobsRemovalHookPlugin>
     */
    protected function getPreProcessorPlugins(): array
    {
        return [
            new InconsistentJobsRemovalHookPlugin(),
        ];
    }
}
