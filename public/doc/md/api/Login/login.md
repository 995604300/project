```
请求方式：POST

请求地址：'/api/Login/login'


返回成功格式：
{
    "error": 0,
    "message": "成功",
    "time": "461.3720703125 ms",
    "data": {
        "access_token": "20rciARAecAOnjMsBg0MXeTTjDlmYODi",
        "expires": 43200,
        "permission": [
            {
                "roleId": "5",
                "permissionId": "5",
                "ROW_NUMBER": "1",
                "permission": {
                    "id": "5",
                    "name": "楼层总控台",
                    "path": "/floorConsole",
                    "ROW_NUMBER": "1"
                }
            },
            {
                "roleId": "5",
                "permissionId": "6",
                "ROW_NUMBER": "2",
                "permission": {
                    "id": "6",
                    "name": "课程管理",
                    "path": "/courseManagement",
                    "ROW_NUMBER": "2"
                }
            }
        ]
    }
}
失败返回格式：
{
    "error": "401",
    "message": "token到期或身份验证失败！",
    "time": "133.05493164062"
}
```

