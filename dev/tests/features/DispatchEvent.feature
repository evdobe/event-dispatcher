Feature: DispatchEvent
    In order to dispatch an event through message
    I should be able to produce a message with event data

Scenario: Dispatch a filtered Event 
    Given The event channel is set
    When an event matching dispatcher filter is inserted in db 
    Then dispatcher should produce a message with event data on event channel
