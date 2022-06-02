<?php
declare(strict_types = 1);

namespace ValanticSpryker\Shared\SchedulerKubernetesZedSchedulerPod;

interface SchedulerKubernetesZedSchedulerPodConstants
{
    /**
     * Kubernetes Service Account Token
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_TOKEN = 'scheduler.kubernetesZedSchedulerPod.token';

    /**
     * Kubernetes Service Account CA Cert
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_CA_CERT_FILE = 'scheduler.kubernetesZedSchedulerPod.ca_cert';

    /**
     * Kubernetes Master URL
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_MASTER = 'scheduler.kubernetesZedSchedulerPod.master';

    /**
     * Kubernetes Namespace
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_NAMESPACE = 'scheduler.kubernetesZedSchedulerPod.namespace';

    /**
     * Kubernetes template path for CronJobs
     *
     * @var string
     */
    public const KUBERNETES_CRONJOB_TEMPLATE_PATH = 'kubernetesZedSchedulerPod.cronjob.template.path';

    /**
     * Restart scheduler pod in case of CronJob updates (create, update, delete)
     *
     * @var string
     */
    public const KUBERNETES_RESTART_SCHEDULER_POD_ON_UPDATES = 'kubernetesZedSchedulerPod.restartSchedulerPodOnUpdates';

    /**
     * Prefix for root Crontab file. Must be an array which is converted to lines at the top of the file.
     *
     * @var string
     */
    public const KUBERNETES_PREFIX_CRONTAB = 'kubernetesZedSchedulerPod.prefixCrontab';
}
