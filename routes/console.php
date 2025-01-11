<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('monitor:website-async')->everyMinute();
