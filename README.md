# Kubernetes scheduler

Kubernetes implementation as an adapter for spryker/scheduler

### Install package
```
composer req valantic-spryker-eco/kubernetes-scheduler
```

### Update shared config
`config/Shared/config_default.php`

```
$config[KernelConstants::CORE_NAMESPACES] = [
    ...
    'ValanticSpryker',
];
```

### Update your shared config for kubernetes
```
$config[SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_MASTER] = 'https://' . getenv('SPRYKER_SCHEDULER_HOST');
$config[SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_CA_CERT_FILE] = getenv('SPRYKER_SCHEDULER_CA_CERT_FILE');
$config[SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_TOKEN] = getenv('DEV_EKS_CRONJOB_ADMIN_TOKEN');
$config[SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_NAMESPACE] = getenv('KUBE_NAMESPACE');
$config[SchedulerKubernetesConstants::KUBERNETES_CRONJOB_TEMPLATE_PATH] = __DIR__ . '/../Zed/cronjobs/kubernetes-cronjob.yaml.twig';
$config[SchedulerKubernetesConstants::KUBERNETES_JOB_TEMPLATE_PATH] = __DIR__ . '/../Zed/cronjobs/kubernetes-job.yaml.twig';

$config[SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_MASTER] = 'https://' . getenv('SPRYKER_SCHEDULER_HOST');
$config[SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_CA_CERT_FILE] = getenv('SPRYKER_SCHEDULER_CA_CERT_FILE');
$config[SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_TOKEN] = getenv('DEV_EKS_CRONJOB_ADMIN_TOKEN');
$config[SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_NAMESPACE] = getenv('KUBE_NAMESPACE');
$config[SchedulerKubernetesZedSchedulerPodConstants::KUBERNETES_CRONJOB_TEMPLATE_PATH] = __DIR__ . '/../Zed/cronjobs/kubernetes-zed-scheduler-pod-cronfile.twig';
$config[SchedulerKubernetesZedSchedulerPodConstants::KUBERNETES_PREFIX_CRONTAB] = ['SHELL=/bin/sh', 'PHP_BIN=/usr/local/bin/php'];
```

### Update your SchedulerDependencyProvider
`\Pyz\Zed\Scheduler\SchedulerDependencyProvider`

```
...

/**
     * @return array<\Spryker\Zed\SchedulerExtension\Dependency\Plugin\SchedulerAdapterPluginInterface>
     */
    protected function getSchedulerAdapterPlugins(): array
    {
        return [
            SchedulerConfig::SCHEDULER_JENKINS => new SchedulerJenkinsAdapterPlugin(),
            SchedulerConfig::SCHEDULER_KUBERNETES => new SchedulerKubernetesAdapterPlugin(),
            SchedulerConfig::SCHEDULER_KUBERNETES_ZED_SCHEDULER_POD => new SchedulerKubernetesZedSchedulerPodAdapterPlugin(),
        ];
    }

...
```
