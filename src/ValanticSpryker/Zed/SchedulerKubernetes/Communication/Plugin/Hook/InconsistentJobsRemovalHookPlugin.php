<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Communication\Plugin\Hook;

use Generated\Shared\Transfer\SchedulerScheduleTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use ValanticSpryker\Zed\SchedulerKubernetes\Dependency\Plugin\Hook\PreProcessSchedulerHookPluginInterface;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\Business\SchedulerKubernetesFacade getFacade()
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig getConfig()
 */
class InconsistentJobsRemovalHookPlugin extends AbstractPlugin implements PreProcessSchedulerHookPluginInterface
{
    /**
     * @inheritDoc
     */
    public function process(SchedulerScheduleTransfer $scheduleTransfer): void
    {
        $this->getFacade()->removeInconsistentJobs($scheduleTransfer);
    }
}
