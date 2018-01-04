# encoding=utf-8

import re
import json
import random
import logging
import os as easy_os

from requests import request
from robot.api import logger


def robot_log(msg):
    logger.info(msg)
    if logging.getLogger("RobotFramework").getEffectiveLevel() <= 10:
        logger.console(msg)


class ApiManage:
    def __init__(self, ip, port=80, host="apimanage.easyops-only.com", user="AutoTest", org="8888"):
        self.target_host = host
        self.target_ip = ip
        self.target_port = port
        self.request_user = user
        self.request_org = org

        self.headers = {
            "host": self.target_host,
            "user": self.request_user,
            "org": self.request_org
        }

    def set_header_key(self, key, val):
        self.headers[key] = val

    def unset_header_key(self, key):
        self.headers.pop(key, None)

    def recovery_headers(self):
        self.headers = {
            "host": self.target_host,
            "user": self.request_user,
            "org": self.request_org
        }

    def _save_download(self, req):
        try:
            content_disposition = req.headers["Content-Disposition"].lower()
            filename = re.findall("filename=\"?([^\"]+)\"?", content_disposition)
            if filename:
                tmp_file = "/tmp/%s_%s" % (random.randint(100000, 999999), filename[0])
            else:
                tmp_file = "/tmp/%s.download" % (random.randint(100000, 999999), filename[0])
            with open(tmp_file, 'wb') as f:
                f.write(req.content)
            return tmp_file
        except Exception, e:
            raise ValueError("Save download file error: %s" % unicode(e))

    def _parse_response(self, req, rsp_type="json"):
        robot_log("Request: %s %s" % (req.request.method, req.url))
        robot_log("Data: %s" % (req.request.body))
        robot_log("Header: %s" % (req.request.headers))
        robot_log('Response: %s, %s' % (req.status_code, req.text))
        if rsp_type == "file":
            return self._save_download(req) if req.status_code == 200 else ''
        elif rsp_type == "json":
            try:
                return req.json()
            except:
                return {}
        else:
            return req.text if req.status_code == 200 else ''

    def _do_http(self, method, url, data={}, files={}, headers={}):
        method = method.lower()
        if method in ["get", "delete"]:
            _req = request(method, url, params=data, headers=headers, timeout=(10, 60))
        else:
            if files:
                _req = request(method, url, data=data, files=files, headers=headers, timeout=(10, 60))
            else:
                headers["content-type"] = 'application/json'
                _req = request(method, url, data=json.dumps(data), headers=headers, timeout=(10, 60))
        return _req

    def get_originBranch(self, projectName):
        _path = "/getOriginBranch/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_originBranch_should_ok(self, projectName):
        result = self.get_originBranch(projectName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_originBranch_should_fail(self, projectName):
        result = self.get_originBranch(projectName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_branchList(self, projectName):
        _path = "/branchList/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_branchList_should_ok(self, projectName):
        result = self.get_branchList(projectName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_branchList_should_fail(self, projectName):
        result = self.get_branchList(projectName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def checkout_branch(self, projectName, branchName):
        _path = "/checkoutBranch/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if branchName is not None:
                _data["branchName"] = branchName
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_checkout_branch_should_ok(self, projectName, branchName):
        result = self.checkout_branch(projectName, branchName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_checkout_branch_should_fail(self, projectName, branchName):
        result = self.checkout_branch(projectName, branchName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def add_interface(self, projectName, serviceName, interfaceName, endpoint, request, response, timeout=None, errors=None):
        _path = "/addInterface/%s/%s" % (projectName, serviceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if errors is not None:
                _data["errors"] = errors
            if interfaceName is not None:
                _data["interfaceName"] = interfaceName
            if request is not None:
                _data["request"] = request
            if endpoint is not None:
                _data["endpoint"] = endpoint
            if timeout is not None:
                _data["timeout"] = timeout
            if response is not None:
                _data["response"] = response
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_add_interface_should_ok(self, projectName, serviceName, interfaceName, endpoint, request, response, timeout=None, errors=None):
        result = self.add_interface(projectName, serviceName, interfaceName, endpoint, request, response, timeout, errors)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_add_interface_should_fail(self, projectName, serviceName, interfaceName, endpoint, request, response, timeout=None, errors=None):
        result = self.add_interface(projectName, serviceName, interfaceName, endpoint, request, response, timeout, errors)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_interfaceList(self, projectName, serviceName):
        _path = "/interfaceList/%s/%s" % (projectName, serviceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_interfaceList_should_ok(self, projectName, serviceName):
        result = self.get_interfaceList(projectName, serviceName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_interfaceList_should_fail(self, projectName, serviceName):
        result = self.get_interfaceList(projectName, serviceName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def update_interface(self, projectName, serviceName, interfaceName, updateInterface):
        _path = "/updateInterface/%s/%s/%s" % (projectName, serviceName, interfaceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = updateInterface
        else:
            if updateInterface is not None:
                _data["updateInterface"] = updateInterface
            

        _files = {}


        _req = self._do_http("put", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_update_interface_should_ok(self, projectName, serviceName, interfaceName, updateInterface):
        result = self.update_interface(projectName, serviceName, interfaceName, updateInterface)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_update_interface_should_fail(self, projectName, serviceName, interfaceName, updateInterface):
        result = self.update_interface(projectName, serviceName, interfaceName, updateInterface)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_interface_detail(self, projectName, serviceName, interfaceName):
        _path = "/interfaceDetail/%s/%s/%s" % (projectName, serviceName, interfaceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_interface_detail_should_ok(self, projectName, serviceName, interfaceName):
        result = self.get_interface_detail(projectName, serviceName, interfaceName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_interface_detail_should_fail(self, projectName, serviceName, interfaceName):
        result = self.get_interface_detail(projectName, serviceName, interfaceName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def delete_interface(self, projectName, serviceName, interfaceName):
        _path = "/deleteInterface/%s/%s/%s" % (projectName, serviceName, interfaceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("delete", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_delete_interface_should_ok(self, projectName, serviceName, interfaceName):
        result = self.delete_interface(projectName, serviceName, interfaceName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_delete_interface_should_fail(self, projectName, serviceName, interfaceName):
        result = self.delete_interface(projectName, serviceName, interfaceName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_interface_name(self, projectName, serviceName, interfaceName):
        _path = "/checkInterfaceName/%s/%s/%s" % (projectName, serviceName, interfaceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_interface_name_should_ok(self, projectName, serviceName, interfaceName):
        result = self.check_interface_name(projectName, serviceName, interfaceName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_interface_name_should_fail(self, projectName, serviceName, interfaceName):
        result = self.check_interface_name(projectName, serviceName, interfaceName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def search_interfaceName(self, projectName, serviceName, interfaceName):
        _path = "/searchInterface/%s/%s" % (projectName, serviceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if interfaceName is not None:
                _data["interfaceName"] = interfaceName
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_search_interfaceName_should_ok(self, projectName, serviceName, interfaceName):
        result = self.search_interfaceName(projectName, serviceName, interfaceName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_search_interfaceName_should_fail(self, projectName, serviceName, interfaceName):
        result = self.search_interfaceName(projectName, serviceName, interfaceName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def add_message(self, project_name, message):
        _path = "/addMessage/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = message
        else:
            if message is not None:
                _data["message"] = message
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_add_message_should_ok(self, project_name, message):
        result = self.add_message(project_name, message)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_add_message_should_fail(self, project_name, message):
        result = self.add_message(project_name, message)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_message_type_template(self, type):
        _path = "/getMessageTypeTemplate/%s" % (type)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_message_type_template_should_ok(self, type):
        result = self.get_message_type_template(type)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_message_type_template_should_fail(self, type):
        result = self.get_message_type_template(type)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_message_list(self, project_name):
        _path = "/getMessageList/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_message_list_should_ok(self, project_name):
        result = self.get_message_list(project_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_message_list_should_fail(self, project_name):
        result = self.get_message_list(project_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_message_name(self, project_name, message_name):
        _path = "/checkMessageName/%s/%s" % (project_name, message_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_message_name_should_ok(self, project_name, message_name):
        result = self.check_message_name(project_name, message_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_message_name_should_fail(self, project_name, message_name):
        result = self.check_message_name(project_name, message_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_message_detail(self, project_name, message_name):
        _path = "/getMessageDetail/%s/%s" % (project_name, message_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_message_detail_should_ok(self, project_name, message_name):
        result = self.get_message_detail(project_name, message_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_message_detail_should_fail(self, project_name, message_name):
        result = self.get_message_detail(project_name, message_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def update_message(self, project_name, message_name, message):
        _path = "/updateMessage/%s/%s" % (project_name, message_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = message
        else:
            if message is not None:
                _data["message"] = message
            

        _files = {}


        _req = self._do_http("put", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_update_message_should_ok(self, project_name, message_name, message):
        result = self.update_message(project_name, message_name, message)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_update_message_should_fail(self, project_name, message_name, message):
        result = self.update_message(project_name, message_name, message)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def delete_message(self, project_name, message_name):
        _path = "/deleteMessage/%s/%s" % (project_name, message_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("delete", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_delete_message_should_ok(self, project_name, message_name):
        result = self.delete_message(project_name, message_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_delete_message_should_fail(self, project_name, message_name):
        result = self.delete_message(project_name, message_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def create_request_and_response_message(self, project_name, service_name, interface_name):
        _path = "/createRequestAndResponseMessage/%s/%s/%s" % (project_name, service_name, interface_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_create_request_and_response_message_should_ok(self, project_name, service_name, interface_name):
        result = self.create_request_and_response_message(project_name, service_name, interface_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_create_request_and_response_message_should_fail(self, project_name, service_name, interface_name):
        result = self.create_request_and_response_message(project_name, service_name, interface_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def create_correct_message(self, project_name, message_name, amount, id):
        _path = "/createCorrectMessage/%s/%s/%s/%s" % (project_name, message_name, amount, id)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_create_correct_message_should_ok(self, project_name, message_name, amount, id):
        result = self.create_correct_message(project_name, message_name, amount, id)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_create_correct_message_should_fail(self, project_name, message_name, amount, id):
        result = self.create_correct_message(project_name, message_name, amount, id)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def create_error_message(self, project_name, message_name):
        _path = "/createErrorMessage/%s/%s" % (project_name, message_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_create_error_message_should_ok(self, project_name, message_name):
        result = self.create_error_message(project_name, message_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_create_error_message_should_fail(self, project_name, message_name):
        result = self.create_error_message(project_name, message_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def search_message(self, projectName, message_name):
        _path = "/searchMessage/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if message_name is not None:
                _data["message_name"] = message_name
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_search_message_should_ok(self, projectName, message_name):
        result = self.search_message(projectName, message_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_search_message_should_fail(self, projectName, message_name):
        result = self.search_message(projectName, message_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def export_message_data_to_file(self, message):
        _path = "/exportMessageDataToFile"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = message
        else:
            if message is not None:
                _data["message"] = message
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_export_message_data_to_file_should_ok(self, message):
        result = self.export_message_data_to_file(message)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_export_message_data_to_file_should_fail(self, message):
        result = self.export_message_data_to_file(message)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_current_create_message_amount(self, id):
        _path = "/getCurrentCreateMessageAmount/%s" % (id)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_current_create_message_amount_should_ok(self, id):
        result = self.get_current_create_message_amount(id)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_current_create_message_amount_should_fail(self, id):
        result = self.get_current_create_message_amount(id)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_regex(self, regex):
        _path = "/checkRegex"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if regex is not None:
                _data["regex"] = regex
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_regex_should_ok(self, regex):
        result = self.check_regex(regex)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_regex_should_fail(self, regex):
        result = self.check_regex(regex)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def add_project(self, url, name):
        _path = "/addProject"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if url is not None:
                _data["url"] = url
            if name is not None:
                _data["name"] = name
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_add_project_should_ok(self, url, name):
        result = self.add_project(url, name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_add_project_should_fail(self, url, name):
        result = self.add_project(url, name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def update_project(self, url, name, latestTime):
        _path = "/updateProject"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if url is not None:
                _data["url"] = url
            if latestTime is not None:
                _data["latestTime"] = latestTime
            if name is not None:
                _data["name"] = name
            

        _files = {}


        _req = self._do_http("put", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_update_project_should_ok(self, url, name, latestTime):
        result = self.update_project(url, name, latestTime)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_update_project_should_fail(self, url, name, latestTime):
        result = self.update_project(url, name, latestTime)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_Project_List(self):
        _path = "/ProjectList"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_Project_List_should_ok(self):
        result = self.get_Project_List()
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_Project_List_should_fail(self):
        result = self.get_Project_List()
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_project_detail(self, project_name):
        _path = "/branchList/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_project_detail_should_ok(self, project_name):
        result = self.get_project_detail(project_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_project_detail_should_fail(self, project_name):
        result = self.get_project_detail(project_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def delete_project(self, project_name):
        _path = "/deleteProject/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("delete", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_delete_project_should_ok(self, project_name):
        result = self.delete_project(project_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_delete_project_should_fail(self, project_name):
        result = self.delete_project(project_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_project_name(self, project_name):
        _path = "/checkProjectName/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_project_name_should_ok(self, project_name):
        result = self.check_project_name(project_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_project_name_should_fail(self, project_name):
        result = self.check_project_name(project_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_project_url(self, project):
        _path = "/checkProjectUrl"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = project
        else:
            if project is not None:
                _data["project"] = project
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_project_url_should_ok(self, project):
        result = self.check_project_url(project)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_project_url_should_fail(self, project):
        result = self.check_project_url(project)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_service_and_interface_amount(self, project_name):
        _path = "/getServiceAndInterfaceAmount/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_service_and_interface_amount_should_ok(self, project_name):
        result = self.get_service_and_interface_amount(project_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_service_and_interface_amount_should_fail(self, project_name):
        result = self.get_service_and_interface_amount(project_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def search_Project(self, name):
        _path = "/searchProject"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if name is not None:
                _data["name"] = name
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_search_Project_should_ok(self, name):
        result = self.search_Project(name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_search_Project_should_fail(self, name):
        result = self.search_Project(name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def add_service(self, project_name, name, protocol=None):
        _path = "/addService/%s" % (project_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if protocol is not None:
                _data["protocol"] = protocol
            if name is not None:
                _data["name"] = name
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_add_service_should_ok(self, project_name, name, protocol=None):
        result = self.add_service(project_name, name, protocol)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_add_service_should_fail(self, project_name, name, protocol=None):
        result = self.add_service(project_name, name, protocol)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_serviceList(self, projectName):
        _path = "/serviceList/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_serviceList_should_ok(self, projectName):
        result = self.get_serviceList(projectName)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_serviceList_should_fail(self, projectName):
        result = self.get_serviceList(projectName)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def update_service(self, projectName, serviceName, updateService):
        _path = "/updateService/%s/%s" % (projectName, serviceName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if True:
            _data = updateService
        else:
            if updateService is not None:
                _data["updateService"] = updateService
            

        _files = {}


        _req = self._do_http("put", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_update_service_should_ok(self, projectName, serviceName, updateService):
        result = self.update_service(projectName, serviceName, updateService)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_update_service_should_fail(self, projectName, serviceName, updateService):
        result = self.update_service(projectName, serviceName, updateService)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def check_service_name(self, project_name, service_name):
        _path = "/checkServiceName/%s/%s" % (project_name, service_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_check_service_name_should_ok(self, project_name, service_name):
        result = self.check_service_name(project_name, service_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_check_service_name_should_fail(self, project_name, service_name):
        result = self.check_service_name(project_name, service_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def delete_service(self, project_name, service_name):
        _path = "/deleteService/%s/%s" % (project_name, service_name)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("delete", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_delete_service_should_ok(self, project_name, service_name):
        result = self.delete_service(project_name, service_name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_delete_service_should_fail(self, project_name, service_name):
        result = self.delete_service(project_name, service_name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def search_service(self, projectName, name):
        _path = "/searchService/%s" % (projectName)
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            if name is not None:
                _data["name"] = name
            

        _files = {}


        _req = self._do_http("post", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_search_service_should_ok(self, projectName, name):
        result = self.search_service(projectName, name)
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_search_service_should_fail(self, projectName, name):
        result = self.search_service(projectName, name)
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result

    def get_Project(self):
        _path = "/ProjectList"
        _url = "http://%s:%s%s" % (self.target_ip, self.target_port, _path)

        # 发送请求
        _data = {}
        if False:
            _data = {}
        else:
            _data = {}
            

        _files = {}


        _req = self._do_http("get", _url, _data, _files, headers=self.headers)
        # 处理返回
        return self._parse_response(_req, "json")

    def test_get_Project_should_ok(self):
        result = self.get_Project()
        if result['code'] != 0:
            raise Exception("Return code %s != 0" % (result['code']))

        return result

    def test_get_Project_should_fail(self):
        result = self.get_Project()
        if result.get('code') is not None and result['code'] == 0:
            raise Exception("Return code %s == 0" % (result['code']))

        return result