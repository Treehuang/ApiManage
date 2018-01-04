<?php

namespace common\library;

class ReturnCode {
    const NETWORK_ERROR = 130100;
    const NETWORK_CONNECTION_FAILED = 130101;
    const NETWORK_DATA_TRANSFER_FAILED = 130102;
    const NETWORK_SSH_CONNECT_FAILED = 130103;
    const NETWORK_SSH_EXEC_ERROR = 130104;
    const NETWORK_EMAIL_CONNECT_FAILED = 130105;
    const NETWORK_EMAIL_SEND_FAILED = 130106;
    const DISK_ERROR = 130200;
    const DISK_CREATE_FILE_FAILED = 130201;
    const DISK_FILE_NOT_EXIST = 130202;
    const DISK_PERMISSION_DENIED = 130203;
    const DISK_FILE_WRITE_FAILED = 130204;
    const DISK_FILE_OPEN_FAILED = 130205;
    const DISK_FILE_READ_FAILED = 130206;
    const DISK_FILE_DELETE_FAILED = 130207;
    const DISK_ILLEGAL_FILE_PATH = 130208;
    const DATABASE_ERROR = 130300;
    const DATABASE_CONNECT_FAILED = 130301;
    const DATABASE_LOGON_FAILED = 130302;
    const DATABASE_QUERY_FAILED = 130303;
    const DATABASE_INSERT_FAILED = 130304;
    const DATABASE_UPDATE_FAILED = 130305;
    const DATABASE_DELETE_FAILED = 130306;
    const DATABASE_REQUEST_TIMEOUT = 130307;
    const DATABASE_NO_RESULT = 130308;
    const DATABASE_NO_AFFECTED = 130309;
    const DATABASE_CREATE_INDEX_FAILED = 130310;
    const DATABASE_FK_CONSTRAINT_FAILED = 130311;
    const DATABASE_INDEX_ALREADY_EXISTS = 130312;
    const DATABASE_DUPLICATE_KEY = 130313;
    const DATABASE_DUPLICATE_DATA = 130314;
    const DATABASE_UPDATE_NOT_MATCH = 130315;
    const DATABASE_KEY_TOO_LARGE_TO_INDEX = 130316;
    const PARAMETER_ERROR = 130500;
    const PARAMETER_MISSING_PARAMETERS = 130501;
    const PARAMETER_FORMAT_ERROR = 130502;
    const PERMISSION_ERROR = 130600;
    const PERMISSION_FUNCTION_DENIED = 130601;
    const PERMISSION_OBJECT_DENIED = 130602;
    const PERMISSION_LICENSE_LIMIT = 130603;
    const LOGICAL_ERROR = 133000;
    const LOGICAL_ACCOUNT_NOT_EXIST = 133001;
    const LOGICAL_USER_NAME_INVALID = 133003;
    const LOGICAL_USER_EMAIL_INVALID = 133004;
    const LOGICAL_USER_PHONE_INVALID = 133005;
    const LOGICAL_USER_PASSWORD_INVALID = 133006;
    const LOGICAL_USER_EMAIL_NOT_VERIFIED = 133007;
    const LOGICAL_USER_VERIFY_CODE_INVALID = 133008;
    const LOGICAL_IDENTIFY_FAILED = 133009;
    const LOGICAL_USER_INVALID = 133010;
    const LOGICAL_USER_NOT_EXIST = 133011;
    const LOGICAL_DEVICE_CREATE_FAILED = 133012;
    const LOGICAL_SYSTEM_LIMIT = 133014;
    const LOGICAL_NOT_FOUND = 133015;
    const LOGICAL_VERIFICATION_CODE_EXPIRED = 133016;
    const LOGICAL_VERIFICATION_CODE_INVALID = 133017;
    const LOGICAL_EXPIRED = 133018;
    const LOGICAL_INVALID = 133019;
    const LOGICAL_USER_GROUP_NOT_EXIST = 133021;
    const LOGICAL_USER_GROUP_NAME_INVALID = 133022;
    const LOGICAL_DIRTY_DATA = 133111;
    const LOGICAL_OBJECT_DEFINE_ERROR = 133112;
    const LOGICAL_ATTRIBUTE_DEFINE_ERROR = 133113;
    const LOGICAL_OBJECT_NOT_EXIST = 133114;
    const LOGICAL_ATTR_ALREADY_EXIST = 133115;
    const LOGICAL_INSTANCE_NOT_EXIST = 133116;
    const LOGICAL_INSTANCE_CONSTRAINT_ERROR = 133117;
    const LOGICAL_INSTANCE_CREATE_FAILED = 133118;
    const LOGICAL_INSTANCE_ARCHIVE_FAILED = 133119;
    const LOGICAL_INSTANCE_ACTIVE_FAILED = 133120;
    const LOGICAL_INSTANCE_IMPORT_FAILED = 133121;
    const LOGICAL_INSTANCE_ALREADY_EXISTS = 133126;
    const LOGICAL_OBJECT_CONSTRAINT_ERROR = 133122;
    const LOGICAL_OBJECT_INSTANCE_NOT_EMPTY = 133123;
    const LOGICAL_OBJECT_IMPORT_FAILED = 133124;
    const LOGICAL_OBJECT_HAS_NO_NAME_ATTRIBUTE = 133125;
    const LOGICAL_OBJECT_RELATION_GROUP_NOT_EXIST = 133127;
    const LOGICAL_OBJECT_RELATION_GROUP_ALREADY_EXIST = 133128;
    const LOGICAL_INVITE_CODE_INVALID = 133201;
    const LOGICAL_USER_ERROR = 133210;
    const LOGICAL_USER_DUPLICATE = 133211;
    const LOGICAL_ATTR_NOT_EXIST = 133212;
    const LOGICAL_QUICK_REGISTER_DONE_PHONE_SEND_FAILED = 133300;
    const LOGICAL_USER_CANNOT_DELETE_YOURSELF = 133301;
    const LOGICAL_USER_CANNOT_CHANGE_YOURSELF_STATE = 133302;
    const LOGICAL_ORG_NOT_EXIST = 133401;
    const LOGICAL_ORG_NOT_VALID = 133402;
    const LOGICAL_ORG_EXPIRED = 133403;

    const ERROR = 400;
}