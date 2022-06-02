<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Twig\Environment;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig;

class CronFileTemplateGenerator implements CronFileTemplateGeneratorInterface
{
    /**
     * @var string
     */
    private const KEY_JOB = 'job';

    private Environment $twig;

    private SchedulerKubernetesZedSchedulerPodConfig $config;

    /**
     * @param \Twig\Environment $twig
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig $config
     */
    public function __construct(
        Environment $twig,
        SchedulerKubernetesZedSchedulerPodConfig $config
    ) {
        $this->twig = $twig;
        $this->config = $config;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateCronFileTemplate(SchedulerJobTransfer $jobTransfer): string
    {
        $jobTransfer
            ->requireName()
            ->requireRepeatPattern()
            ->requireCommand()
            ->requireStore();

        $templateBasename = basename($this->config->getCronJobTemplatePath());

        return trim($this->twig->render($templateBasename, [static::KEY_JOB => $jobTransfer->toArray()]));
    }
}
