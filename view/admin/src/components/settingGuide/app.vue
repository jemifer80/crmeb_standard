<template>
  <Tabs value="1" :animated="false">
    <TabPane label="1.注册开放平台" name="1">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">打开开放平台</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  在微信开放平台官网首页：<a
                    href="https://open.weixin.qq.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    >https://open.weixin.qq.com</a
                  >
                </div>
                <div>点击右上角“注册”</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/open-1-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">完善信息</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>根据提示，完善信息即可完成注册</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/open-1-2-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="2.开发者资质认证" name="2">
      <Timeline>
        <TimelineItem>
<!--          <div class="dot" slot="dot">1</div>-->
          <div class="title">开发者资质认证</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  开发者资质认证（跟微信公众号一样，需要每年300元服务费）
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/open-2-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="3.创建应用" name="3">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">关联绑定相关应用</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  可根据自己的情况，关联绑定 APP、PC网站、公众号、小程序等
                </div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/open-3-1-1.png`" alt="" />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">获取Appid以及Secret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>创建之后可以点击进入获取开放平台appid以及secret</div>
              </div>
              <div class="image">
                <img :src="`${baseURL}/statics/system/open-3-2-1.png`" alt="" />
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
  name: 'appGuide',
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
/deep/.ivu-timeline-item-head-blue{
  border:0
}
.ivu-tabs {
  padding: 0 !important;
}

.ivu-tabs>>>.ivu-tabs-tab:nth-child(2)::before {
  display: none;
}
</style>
