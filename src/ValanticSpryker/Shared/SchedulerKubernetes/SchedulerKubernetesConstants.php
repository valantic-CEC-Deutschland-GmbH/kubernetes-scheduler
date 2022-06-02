<?php
declare(strict_types = 1);

namespace ValanticSpryker\Shared\SchedulerKubernetes;

interface SchedulerKubernetesConstants
{
    /**
     * Kubernetes Service Account Token
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_TOKEN = 'scheduler.kubernetes.token';

    /**
     * Kubernetes Service Account CA Cert
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_CA_CERT_FILE = 'scheduler.kubernetes.ca_cert';

    /**
     * Kubernetes Master URL
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_MASTER = 'scheduler.kubernetes.master';

    /**
     * Kubernetes Namespace
     *
     * @var string
     */
    public const SCHEDULER_KUBERNETES_NAMESPACE = 'scheduler.kubernetes.namespace';

    /**
     * Kubernetes template path for CronJobs
     *
     * @var string
     */
    public const KUBERNETES_CRONJOB_TEMPLATE_PATH = 'kubernetes.cronjob.template.path';

    /**
     * Kubernetes template path for CronJobs
     *
     * @var string
     */
    public const KUBERNETES_JOB_TEMPLATE_PATH = 'kubernetes.job.template.path';
}
