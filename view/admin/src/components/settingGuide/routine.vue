<template>
  <Tabs value="1" :animated="false">
    <TabPane label="1.注册小程序" name="1">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">打开公众平台</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  打开微信公众平台官网：<a
                    href="https://mp.weixin.qq.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://mp.weixin.qq.com</a
                  >
                </div>
                <div>右上角点击“立即注册”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}statics/system/mp-1-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">选择账号类型</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>选择账号类型，请选择“小程序”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-1-2-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">填写邮箱并激活</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>登录您的邮箱，查看激活邮件，然后在此填写邮箱验证码</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-1-3-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">4</div>
          <div class="title">信息登记</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  主体请记得选择“企业”，之后的企业信息可根据您自己的企业情况填写
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-1-4-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="2.完善小程序" name="2">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">添加开发者</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>如果本人为小程序管理者可以跳过此步</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-2-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">重置AppSecret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：点击小程序左侧菜单，开发 → 开发管理 → 开发设置</div>
                <div>
                  操作：点击图中的重置按钮，重置成功后记得保存AppID(小程序ID)、AppSecret(小程序密钥)
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-2-2-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">关闭IP白名单</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>位置：点击小程序左侧菜单，开发 → 开发管理 → 开发设置</div>
                <div>
                  操作：点击图中的关闭按钮，此开关需要管理员的微信扫码才可以关闭哦
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-2-3-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">4</div>
          <div class="title">配置服务器</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  填写您的域名，请注意填写域名的前缀，按照提示格式填写哦
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-2-4-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">5</div>
          <div class="title">修改业务域名</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>修改业务域名，同样填写上一步的域名即可</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-2-5-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="3.开发配置" name="3">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">登录您的crmeb系统后台</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  位置：设置 → 应用设置 →
                  小程序，填写小程序开发者信息，AppID、AppSecret、小程序名称
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-3-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">关闭小程序充值开关</div>
          <div class="content">
            <div class="item">
              <Alert show-icon>
                <div>目前小程序审核，带有充值功能会被要求提供相关资料（预付卡在线充值业务），CRMEB小程序提交前，
                  请在后台关闭充值功能，等待审核通过后再打开</div>
              </Alert>
              <div class="text">
                <div>
                  位置：营销→余额充值→充值设置，小程序充值开关，选择“关闭”（不要忘记底部的提交按钮哦~）
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/mp-3-2-1.png`" alt="" />
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
  name: 'routine',
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
