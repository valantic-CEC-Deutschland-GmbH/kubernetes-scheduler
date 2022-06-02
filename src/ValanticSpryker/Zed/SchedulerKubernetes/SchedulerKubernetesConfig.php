<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use ValanticSpryker\Shared\SchedulerKubernetes\SchedulerKubernetesConstants;

class SchedulerKubernetesConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    private const KUBERNETES_MASTER = 'master';

    /**
     * @var string
     */
    private const KUBERNETES_CA_CERT = 'ca_cert';

    /**
     * @var string
     */
    private const KUBERNETES_TOKEN = 'token';

    /**
     * @var string
     */
    private const KUBERNETES_NAMESPACE = 'namespace';

    /**
     * @return array
     */
    public function getKubernetesConfig(): array
    {
        return [
            self::KUBERNETES_MASTER => $this->get(SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_MASTER),
            self::KUBERNETES_CA_CERT => $this->get(SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_CA_CERT_FILE),
            self::KUBERNETES_TOKEN => $this->get(SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_TOKEN),
            self::KUBERNETES_NAMESPACE => $this->get(SchedulerKubernetesConstants::SCHEDULER_KUBERNETES_NAMESPACE),
        ];
    }

    /**
     * @return string
     */
    public function getCronJobTemplatePath(): string
    {
        return $this->get(SchedulerKubernetesConstants::KUBERNETES_CRONJOB_TEMPLATE_PATH);
    }

    /**
     * @return string
     */
    public function getJobTemplatePath(): string
    {
        return $this->get(SchedulerKubernetesConstants::KUBERNETES_JOB_TEMPLATE_PATH);
    }

    /**
     * @return array
     */
    public function getKubernetesTemplateFolders(): array
    {
        return array_unique([
            dirname($this->getCronJobTemplatePath()),
            dirname($this->getJobTemplatePath()),
        ]);
    }
}
