```
请求方式：GET

请求地址：'/api/Plate/index'


返回成功格式：
{
    "error": 0,
    "message": "成功",
    "time": "180.18212890625 ms",
    "data": {
        "total": 3,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "data": [
            {
                "id": "1",
                "PlateName": "多媒体小教室",
                "IP": "192.168.0.1",
                "SN": "12312312312",
                "ClassroomId": "4",
                "ROW_NUMBER": "1",
                "classroom": {
                    "id": "4",
                    "classroomName": "多媒体小教室",
                    "ROW_NUMBER": "1"
                }
            },
            {
                "id": "2",
                "PlateName": "多媒体大教室前门",
                "IP": "192.168.0.1",
                "SN": "1111111111",
                "ClassroomId": "5",
                "ROW_NUMBER": "2",
                "classroom": {
                    "id": "5",
                    "classroomName": "多媒体大教室",
                    "ROW_NUMBER": "2"
                }
            },
            {
                "id": "3",
                "PlateName": "多媒体大教室后门",
                "IP": "192.168.0.1",
                "SN": "2222222222",
                "ClassroomId": "5",
                "ROW_NUMBER": "3",
                "classroom": {
                    "id": "5",
                    "classroomName": "多媒体大教室",
                    "ROW_NUMBER": "2"
                }
            }
        ]
    }
}
失败返回格式：
{
    "error": "1",
    "message": "失败",
    "time": "19.589111328125"
}

```

