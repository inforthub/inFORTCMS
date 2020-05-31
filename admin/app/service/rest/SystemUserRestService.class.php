<?php
/**
 * SystemUser REST service
 */
class SystemUserRestService extends AdiantiRecordService
{
    const DATABASE      = 'permission';
    const ACTIVE_RECORD = 'SystemUser';
    const ATTRIBUTES    = ['id', 'name', 'login', 'email', 'system_unit_id', 'active'];
}
