<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator;

use Generated\Shared\Transfer\SchedulerJobTransfer;

interface KubernetesTemplateGeneratorInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateCronJobTemplate(SchedulerJobTransfer $jobTransfer): string;

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateJobTemplate(SchedulerJobTransfer $jobTransfer): string;
}
