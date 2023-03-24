FROM leekay0218/crmeb-pro

## 复制代码
## 在本地调试注释掉，使用映射把文件映射进去
#ADD ./ /var/www

# 设置容器启动后的默认运行目录
WORKDIR /var/www

# 默认入口命令
ENTRYPOINT ["/entrypoint.sh"]

# 本地调试进入容器后手动执行命令，如果是部署项目的话可以打开注释
#CMD ["php", "think", "swoole"]
