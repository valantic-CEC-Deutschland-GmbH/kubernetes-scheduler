<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator;

use Generated\Shared\Transfer\SchedulerJobTransfer;

interface CronFileTemplateGeneratorInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateCronFileTemplate(SchedulerJobTransfer $jobTransfer): string;
}
