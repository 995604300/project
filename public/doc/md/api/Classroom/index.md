```
请求方式：GET

请求地址：'/api/Classroom/index'


返回成功格式：
{
    "error": 0,
    "message": "成功",
    "time": "73.974853515625 ms",
    "data": {
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "data": [
            {
                "id": 11,
                "exam_id": 1,
                "subject_num": 1,
                "facuilty_num": 1,
                "specialty_num": 1,
                "date": "2019-10-10",
                "start_time": "10:00:00",
                "end_time": "12:00:00",
                "teacher_num1": 0,
                "teacher_num2": 0,
                "create_time": "2019-10-10 16:17:05",
                "update_time": "2019-10-10 16:17:05",
                "exam": {
                    "id": 1,
                    "name": "16级2019年下半学期期中考试",
                    "type": 5,
                    "exam_type": {
                        "id": 5,
                        "name": "期中考试"
                    }
                },
                "facuilty": {
                    "yx_code": 1,
                    "name": "中文系"
                },
                "specialty": {
                    "zy_code": 1,
                    "name": "计算机"
                },
                "subject": {
                    "code": 1,
                    "name": "计算机导论"
                }
            },
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

