<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Communication\Plugin\Adapter;

use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SchedulerExtension\Dependency\Plugin\SchedulerAdapterPluginInterface;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\Business\SchedulerKubernetesFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig getConfig()
 */
class SchedulerKubernetesAdapterPlugin extends AbstractPlugin implements SchedulerAdapterPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function setup(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFacade()->setupCronJobs($schedulerScheduleTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function clean(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFacade()->cleanCronJobs($schedulerScheduleTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function suspend(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFacade()->suspendCronJobs($schedulerScheduleTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function resume(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFacade()->resumeCronJobs($schedulerScheduleTransfer);
    }
}
