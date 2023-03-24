<template>
  <Tabs value="1" :animated="false">
    <TabPane label="1.基础配置" name="1">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">注册企业微信</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  打开企业微信官网首页：<a
                    href="https://work.weixin.qq.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://work.weixin.qq.com/</a
                  >
                </div>
                <div>点击图片上的“立即注册”，注册后进行下一步</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-1-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">获取企业ID</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  登录企业微信管理后台：<a
                    href="https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu</a
                  >
                </div>
                <div>位置：我的企业 → 企业信息，在最下方找到企业ID并复制</div>
              </div>
            </div>
            <div class="image">
              <img :src="`${baseURL}statics/system/work-1-1-2.png`" alt="" />
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">前往商城后台填写企业ID</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>登录商城后台：http://您的域名/admin/work/config</div>
                <div>
                  位置：企业微信 → 企业微信设置，将企业ID复制到此处并保存
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-1-1-3.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="2.通讯录配置" name="2">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">开启API接口同步</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  登录企业微信管理后台：<a
                    href="https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu</a
                  >
                </div>
                <div>位置：管理工具 → 通讯录同步，点击“开启API接口同步”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">通讯录同步</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.权限一栏，勾选“API编辑通讯录”以及“开启手动编辑”；</div>
                <div>2.点击查看Secret，然后打开企业微信客户端进行复制</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-2-1.png`" alt="" />
                <img :src="`${baseURL}/statics/system/work-2-2-2.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">商城后台填写Secret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  将复制的secret填写到商城后台端的 企业微信通讯录配置 → Secret
                  中，如下图
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-3-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">4</div>
          <div class="title">企业可信IP</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.点击企业可信IP一栏的“编辑”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-4-1.png`" alt="" />
              </div>
            </div>
            <div class="item">
              <div class="text">
                <div>2.点击“确认操作”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-4-2.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">5</div>
          <div class="title">打开接收事件服务器并编辑</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.位置：企业微信管理后台 → 管理工具 → 通讯录同步</div>
                <div>
                  操作：点击编辑进入配置页面（截图为已经配置后），需要当前登录的管理员在手机上确认进入编辑页面
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-5-1.png`" alt="" />
              </div>
            </div>
            <div class="item">
              <div class="text">
                <div>
                  2.进入编辑页面后，需要返回商城后台并复制下图1中的
                  Token、EncodingAESKey、服务器地址到下图2中对应的位置中，点击保存。直到提示配置成功
                </div>
              </div>
              <Alert show-icon
                >这里的Token、EncodingAESKey、服务器地址将会在其他事件(如3.客户配置)接收器中使用到。</Alert
              >
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-2-5-2.png`" alt="" />
                <img :src="`${baseURL}/statics/system/work-2-5-3.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="3.客户配置" name="3">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">获取Secret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  登录企业微信管理后台：<a
                    href="https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu</a
                  >
                </div>
                <div>位置：客户与上下游 → 客户联系 → 客户</div>
                <div>
                  操作：点击“API”，首先需要绑定微信开发者ID，然后点击Secret一栏的查看按钮并返回企微客户端进行复制
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-3-1-1.png`" alt="" />
                <img :src="`${baseURL}/statics/system/work-3-1-2.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">商城后台填写Secret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  将复制的secret填写到商城后台端的 企业微信客户配置 → Secret
                  中，如下图
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-3-2-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">打开接收事件服务器并编辑</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.位置：客户与上下游 → 客户联系 → 客户</div>
                <div>
                  操作：点击编辑进入配置页面（截图为已经配置后），需要当前登录的管理员在手机上确认进入编辑页面
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-3-3-1.png`" alt="" />
              </div>
            </div>
            <div class="item">
              <div class="text">
                <div>
                  2.进入编辑页面后，需要返回商城后台并复制下图1中的Token、EncodingAESKey、服务器地址到下图2中对应的位置中，点击保存。直到提示配置成功
                </div>
              </div>
              <Alert show-icon
                >客户事件接收的配置和企业微信通讯录一样，只要复制出刚配置的企业微信通讯录的配置，填入当前客户接受事件服务器中就可以</Alert
              >
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-3-3-2.png`" alt="" />
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-3-3-3.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="4.自建应用设置" name="4">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">获取Secret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  登录企业微信管理后台：<a
                    href="https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://work.weixin.qq.com/wework_admin/loginpage_wx?from=myhome_baidu</a
                  >
                </div>
                <div>位置：应用管理 → 应用 → 自建</div>
                <div>
                  操作：进入创建应用页面，上传应用LOGO、设置应用名称、设置应用介绍、设置可见部门，然后点击创建应用进行创建
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">网页授权及JS-SDK</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：刚创建的应用界面 → 网页授权及JS-SDK</div>
                <div>
                  操作1：把自己的域名填写在下图两个输入框中，格式例如：www.crmeb.com
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-2-1.png`" alt="" />
              </div>
            </div>
            <div class="item">
              <div class="text">
                操作2：点击“申请校验域名”下载文件上传到服务器根目录下的public里。使用域名加文件名称需要能访问到此文件就算
                成功，最后点击【确定】提交可信域名配置
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-2-2.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">企业微信授权登录</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：刚创建的应用界面 → 企业微信授权登录</div>
                <div>
                  操作：点击“设置授权回调域”填写入自己的域名(例如：www.crmeb.com)，然后点击“保存”
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-3-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">4</div>
          <div class="title">配置到聊天工具栏</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.位置：刚创建的应用界面 → 配置到聊天工具栏</div>
                <div>2.操作：点击“配置页面”；</div>
                <div>
                  页面内容：选择“自定义”，填写连接：https://自己的域名/pages/work/userInfo/index
                </div>
                <div>配置到： 选择“客户联系聊天工具栏“</div>
                <div>
                  保存此配置，就可以在企业微信和客户聊天或者群聊中看到刚才配置的侧边栏
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-4-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">5</div>
          <div class="title">企业可信IP</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：刚创建的应用界面 → 企业可信IP</div>
                <div>目前通讯录权限由自建应用获取，一定需要配置这里</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-5-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">6</div>
          <div class="title">调用应用</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：客户上下游 → 客户联系 → 客户 → API → 可调用应用</div>
                <div>
                  操作：勾选上刚才创建的自定义应用，点击确定就可以使用该应用
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/work-4-6-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
  </Tabs>
</template>

<script>
import Setting from '@/setting';
export default {
  name: 'work',
  data() {
    return {
      baseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
    };
  },
};
</script>

<style lang="stylus" scoped>
/deep/.ivu-tabs-nav-wrap{
  border-radius: 5px;
}
/deep/.ivu-tabs-bar{
  border-bottom: 0;
}
/deep/.ivu-timeline-item-content{
  padding-bottom: 40px !important;
.title{
  font-weight: 600;
}
}
.ivu-tabs {
  padding: 0 !important;
}

.ivu-tabs>>>.ivu-tabs-tab:nth-child(2)::before {
  display: none;
}
</style>
