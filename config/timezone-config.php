<?php

(new \Darkheim\Infrastructure\Bootstrap\TimezoneInitializer(
    new \Darkheim\Infrastructure\Bootstrap\ConfigProvider(__DIR__)
))->apply();
