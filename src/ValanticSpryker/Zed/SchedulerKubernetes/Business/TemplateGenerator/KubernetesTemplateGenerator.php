<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Twig\Environment;
use ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig;

class KubernetesTemplateGenerator implements KubernetesTemplateGeneratorInterface
{
    /**
     * @var string
     */
    private const KEY_JOB = 'job';

    private \Twig\Environment $twig;

    private \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig $schedulerKubernetesConfig;

    /**
     * @param \Twig\Environment $twig
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\SchedulerKubernetesConfig $schedulerKubernetesConfig
     */
    public function __construct(
        Environment $twig,
        SchedulerKubernetesConfig $schedulerKubernetesConfig
    ) {
        $this->twig = $twig;
        $this->schedulerKubernetesConfig = $schedulerKubernetesConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateCronJobTemplate(SchedulerJobTransfer $jobTransfer): string
    {
        $jobTransfer
            ->requireRepeatPattern()
            ->requireCommand()
            ->requireStore();

        $templateBasename = basename($this->schedulerKubernetesConfig->getCronJobTemplatePath());

        return $this->twig->render($templateBasename, [static::KEY_JOB => $jobTransfer->toArray()]);
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return string
     */
    public function generateJobTemplate(SchedulerJobTransfer $jobTransfer): string
    {
        $jobTransfer
            ->requireRepeatPattern()
            ->requireCommand()
            ->requireStore();

        $templateBasename = basename($this->schedulerKubernetesConfig->getJobTemplatePath());

        return $this->twig->render($templateBasename, [static::KEY_JOB => $jobTransfer->toArray()]);
    }
}
