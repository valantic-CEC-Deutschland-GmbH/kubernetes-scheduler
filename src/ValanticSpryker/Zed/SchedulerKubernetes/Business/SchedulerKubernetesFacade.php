<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business;

use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \ValanticSpryker\Zed\SchedulerKubernetes\Business\SchedulerKubernetesBusinessFactory getFactory()
 */
class SchedulerKubernetesFacade extends AbstractFacade implements SchedulerKubernetesFacadeInterface
{
    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function setupCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFactory()
            ->createSchedulerKubernetesSetup()
            ->processSchedule($schedulerScheduleTransfer);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function cleanCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFactory()
            ->createSchedulerKubernetesClean()
            ->processSchedule($schedulerScheduleTransfer);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function suspendCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFactory()
            ->createSchedulerKubernetesDisable()
            ->processSchedule($schedulerScheduleTransfer);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function resumeCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer
    {
        return $this->getFactory()
            ->createSchedulerKubernetesEnable()
            ->processSchedule($schedulerScheduleTransfer);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return void
     */
    public function removeInconsistentJobs(SchedulerScheduleTransfer $scheduleTransfer): void
    {
        $this->getFactory()->createInconsistentJobsRemover()->remove($scheduleTransfer);
    }
}
