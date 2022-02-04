#!/bin/bash
if [ -z "$ENVIRONMENT" ]
then
    echo $ENVIRONMENT
    ENVIRONMENT=$1
    shift 1
fi