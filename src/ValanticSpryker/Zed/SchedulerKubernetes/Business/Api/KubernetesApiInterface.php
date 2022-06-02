<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Api;

use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;

interface KubernetesApiInterface
{
    /**
     * Specification:
     * - returns a name list of Spryker CronJobs
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getCronJobs(): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - returns a name list of Spryker Jobs
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getJobs(): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - returns a name list of Spryker jobs (CronJobs and Jobs)
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function getAllJobs(): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - create a CronJob definition
     * - does not start a job immediately
     *
     * @param string $jobName
     * @param string $cronJobTemplate
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function createCronJob(string $jobName, string $cronJobTemplate): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - create a Job definition
     * - starts a job immediately (due to the nature of Jobs)
     *
     * @param string $jobName
     * @param string $jobTemplate
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function createJob(string $jobName, string $jobTemplate): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - removes a CronJob
     * - existing execution is not stopped
     *
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function deleteCronJob(string $jobName): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - removes a Job
     * - existing execution is stopped indirectly due ot the nature of Jobs
     *
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function deleteJob(string $jobName): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - marks a job not to be scheduled anymore
     * - existing execution is not stopped
     *
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function disableCronJob(string $jobName): SchedulerKubernetesResponseTransfer;

    /**
     * Specification:
     * - removes Jobs for specified name
     * - removes Pods for specified CronJob or Job name
     * - also removes pods in error state
     *
     * @param string $jobName
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function stopExecution(string $jobName): SchedulerKubernetesResponseTransfer;
}
