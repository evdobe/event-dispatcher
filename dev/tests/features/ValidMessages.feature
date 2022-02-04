Feature: ValidMessage
    In order to make an event available to a service
    I should be able to insert the event data into event database table

Scenario: Insert a valid message from an unfilterred and untranslated channel
    Given The channel is set
    When listener encounters an valid message 
    Then it should insert it in db
