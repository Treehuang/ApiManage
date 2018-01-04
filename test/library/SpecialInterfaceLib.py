# encoding=utf-8

import logging

from robot.api import logger
from ApiManage import ApiManage


def robot_log(msg):
    logger.info(msg)
    if logging.getLogger("RobotFramework").getEffectiveLevel() <= 10:
        logger.console(msg)


class SpecialInterfaceLib(ApiManage):

    def test_get_current_create_message_amount_should_fail(self, id):
        result = self.get_current_create_message_amount(id)

        return result

