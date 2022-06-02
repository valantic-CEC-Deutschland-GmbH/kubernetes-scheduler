<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy;

interface ExecutionStrategyBuilderInterface
{
    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyInterface
     */
    public function buildExecutionStrategy(): ExecutionStrategyInterface;
}
