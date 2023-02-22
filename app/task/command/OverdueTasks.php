<?php

namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;

class OverdueTasks extends Command
{
    protected function configure()
    {
        $this->setName('task:OverdueTasks')
            ->setDescription('逾期任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $time   =   time();
        Db::name('task')->where("task_end_time <$time")->update(['task_status'=>4]);
    }


}
