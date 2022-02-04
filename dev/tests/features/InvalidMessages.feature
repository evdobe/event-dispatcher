Feature: InvalidMessage
    In order to keep a record of invalid messages
    As an administrator
    I should be able to consume invalid messages from invalid queue

Scenario: Send invalid message to invalid queue
    Given The invalid channel is set
    When listener encounters an invalid message 
    Then it should republish it on invalid channel
