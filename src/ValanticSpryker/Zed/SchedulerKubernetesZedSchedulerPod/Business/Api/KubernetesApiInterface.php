<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api;

use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;

interface KubernetesApiInterface
{
    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getJobList(): SchedulerKubernetesResponseTransfer;

    /**
     * @param string $jobName
     * @param string $crontabFileContents
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function addToConfigMap(string $jobName, string $crontabFileContents): SchedulerKubernetesResponseTransfer;

    /**
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function removeFromConfigMap(string $jobName): SchedulerKubernetesResponseTransfer;

    /**
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function restartScheduler(): SchedulerKubernetesResponseTransfer;
}
