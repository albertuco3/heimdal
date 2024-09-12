<?php

namespace App\Service;

use App\Entity\UserPoint;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use AutoMapperPlus\Configuration\MappingInterface;
use AutoMapperPlus\MappingOperation\Operation;

class AutoMapper extends \AutoMapperPlus\AutoMapper {
    public function __construct() {

        $config = new AutoMapperConfig();
        $this->registerDtoMapping($config, UserPoint::class)
             ->forMember('id', fn(UserPoint $source) => $source->getId())
             ->forMember('points', fn(UserPoint $source) => $source->getPoints())
             ->forMember('reason', fn(UserPoint $source) => $source->getReason())
             ->forMember('date', fn(UserPoint $source) => $source->getDate()->format('Y-m-d H:i:s'));

        parent::__construct($config);
    }

    protected function registerDtoMapping(AutoMapperConfig $config, $class): MappingInterface {
        return $config->registerMapping($class, \stdClass::class)
                      ->withDefaultOperation(Operation::ignore());
    }

    public function mapToDto($source) {
        return $this->map($source, \stdClass::class);
    }

    public function mapMultipleToDto($source): array {
        return $this->mapMultiple($source, \stdClass::class);
    }
}