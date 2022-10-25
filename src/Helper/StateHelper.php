<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Exception\ObtainingStateFailedException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\System\StateMachine\StateMachineEntity;

class StateHelper
{
    /**
     * @param StateMachineEntity $stateMachine
     * @param string|null $name
     * @param string|null $fallback
     * @return string The state ID
     * @throws ObtainingStateFailedException
     */
    public static function getIdFromTechnicalName(StateMachineEntity $stateMachine, ?string $name = null, ?string $fallback = null): string
    {
        $initialState = $stateMachine->getInitialState();

        if (is_null($initialState)) {
            throw new ObtainingStateFailedException($stateMachine->getName());
        }

        $stateId = $initialState->getId();

        if ($name !== null) {
            foreach ($stateMachine->getStates() as $state) {
                if ($state->getTechnicalName() === $name) {
                    return $state->getId();
                }
            }
        }
        if ($fallback !== null) {
            foreach ($stateMachine->getStates() as $state) {
                if ($state->getTechnicalName() === $fallback) {
                    return $state->getId();
                }
            }
        }
        return $stateId;
    }

    public static function toArray(StateMachineEntity $stateMachine): array
    {
        $states = [];
        foreach ($stateMachine->getStates() as $state) {
            $states[$state->getTechnicalName()] = $state->getId();
        }
        return $states;
    }

}