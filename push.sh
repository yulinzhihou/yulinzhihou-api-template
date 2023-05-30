#!/usr/bin/env bash
# author yulinzhihou@gmail.com
# data: 2023-05-30
# 用于上传代码到代码仓库。提前建立好分支与线上的绑定关系
# 参数1：提交代码的信息
git add -A
git commit -m "$1"
git push origin