<?php

namespace Webkul\MaterialInventory\Enums;

enum MaterialTransactionType: string
{
    case Register = 'register';
    case CheckOut = 'check_out';
    case CheckIn = 'check_in';
    case SendRepair = 'send_repair';
    case ReturnFromRepair = 'return_from_repair';
    case AssignProject = 'assign_project';
    case RemoveFromProject = 'remove_from_project';
    case TransferCustody = 'transfer_custody';
    case ConditionChange = 'condition_change';
    case Retire = 'retire';
    case MarkLost = 'mark_lost';
    case MarkFound = 'mark_found';
}
