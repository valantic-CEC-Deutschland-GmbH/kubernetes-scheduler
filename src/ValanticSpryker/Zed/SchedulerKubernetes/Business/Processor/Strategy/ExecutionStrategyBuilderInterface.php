<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy;

interface ExecutionStrategyBuilderInterface
{
    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategyInterface
     */
    public function buildExecutionStrategy(): ExecutionStrategyInterface;
}
