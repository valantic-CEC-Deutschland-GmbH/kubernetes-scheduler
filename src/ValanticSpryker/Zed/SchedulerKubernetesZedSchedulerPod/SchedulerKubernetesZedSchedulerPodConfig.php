<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use ValanticSpryker\Shared\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConstants;

class SchedulerKubernetesZedSchedulerPodConfig extends AbstractBundleConfig
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
            self::KUBERNETES_MASTER => $this->get(SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_MASTER),
            self::KUBERNETES_CA_CERT => $this->get(SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_CA_CERT_FILE),
            self::KUBERNETES_TOKEN => $this->get(SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_TOKEN),
            self::KUBERNETES_NAMESPACE => $this->get(SchedulerKubernetesZedSchedulerPodConstants::SCHEDULER_KUBERNETES_NAMESPACE),
        ];
    }

    /**
     * @return string
     */
    public function getCronJobTemplatePath(): string
    {
        return $this->get(SchedulerKubernetesZedSchedulerPodConstants::KUBERNETES_CRONJOB_TEMPLATE_PATH);
    }

    /**
     * @return array
     */
    public function getKubernetesTemplateFolders(): array
    {
        return array_unique([
            dirname($this->getCronJobTemplatePath()),
        ]);
    }

    /**
     * @return bool
     */
    public function restartSchedulerPodOnUpdate(): bool
    {
        return (bool)$this->get(SchedulerKubernetesZedSchedulerPodConstants::KUBERNETES_RESTART_SCHEDULER_POD_ON_UPDATES, false);
    }

    /**
     * @return array
     */
    public function getCrontabPrefix(): array
    {
        return $this->get(SchedulerKubernetesZedSchedulerPodConstants::KUBERNETES_PREFIX_CRONTAB, []);
    }
}
