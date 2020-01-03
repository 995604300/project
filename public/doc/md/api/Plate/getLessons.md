```
请求方式：POST

请求地址：'/api/Plate/getLessons'


返回成功格式：
{
    "error": 0,
    "message": "成功",
    "time": "981.10107421875 ms",
    "data": {
        "id": "3",
        "PlateName": "多媒体大教室后门",
        "IP": "192.168.0.1",
        "SN": "2222222222",
        "ClassroomId": "5",
        "ROW_NUMBER": "1",
        "lesson": [
            {
                "id": "3",
                "date": "2019-12-26",
                "startTime": "09:00:00.0000000",
                "endTime": "11:00:00.0000000",
                "lessonName": "1111",
                "classroomId": "5",
                "teacherName": "",
                "week": "",
                "ROW_NUMBER": "1"
            },
            {
                "id": "8",
                "date": "2019-12-28",
                "startTime": "09:00:00.0000000",
                "endTime": "11:00:00.0000000",
                "lessonName": "看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度",
                "classroomId": "5",
                "teacherName": "",
                "week": "",
                "ROW_NUMBER": "2"
            },
            {
                "id": "10",
                "date": "2019-12-28",
                "startTime": "16:00:00.0000000",
                "endTime": "15:40:00.0000000",
                "lessonName": "看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度看我长度",
                "classroomId": "5",
                "teacherName": "",
                "week": "",
                "ROW_NUMBER": "3"
            }
        ],
        "classroom": {
            "id": "5",
            "classroomName": "多媒体大教室",
            "ROW_NUMBER": "1"
        }
    }
}
失败返回格式：
{
    "error": "1",
    "message": "失败",
    "time": "19.589111328125"
}

```

