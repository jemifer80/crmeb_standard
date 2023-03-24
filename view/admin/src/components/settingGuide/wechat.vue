<template>
  <Tabs value="1" :animated="false">
    <TabPane label="1.注册公众号" name="1">
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
                <img
                  :src="`${baseURL}/statics/system/wechat-1-1-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">选择账号类型：服务号</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>在选择注册账号类型中必须选择“服务号”</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-1-2-1.png`"
                  alt=""
                />
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
                <img
                  :src="`${baseURL}/statics/system/wechat-1-3-1.png`"
                  alt=""
                />
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
                  公司请记得选择
                  企业→企业类型，之后的企业信息可根据您自己的企业情况填写
                </div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-1-4-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">5</div>
          <div class="title">填写公众号信息</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>填写公众号信息，包括帐号名称、功能介绍、选择运营地区</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-1-5-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">6</div>
          <div class="title">验证账号</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>根据所选择的验证方式，完成验证/认证后帐号功能即可使用</div>
              </div>
            </div>
          </div>
        </TimelineItem>
      </Timeline>
    </TabPane>
    <TabPane label="2.公众号配置" name="2">
      <Timeline>
        <TimelineItem>
          <div class="dot" slot="dot">1</div>
          <div class="title">登录公众平台</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>点击左侧菜单，设置→公众设置</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-1-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">功能设置中配置3个域名</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  在功能设置中，配置“业务域名”、“JS接口安全域名”、“网页授权域名”
                </div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-2-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">配置域名</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  配置域名，直接填写你所绑定配置的域名即可，填写域名的注意事项。
                </div>
              </div>
              <Alert show-icon>
                <div>注意事项：</div>
                <div>1. 填写的域名不支持IP地址、端口号及短链域名；</div>
                <div>2. 域名须通过<span>ICP备案</span></div>
                <div>
                  3. 将验证文件上传至<span>public</span>下，并确保可以访问；
                </div>
                <div>4. 一个自然月内最多可修改并保存三次。</div>
              </Alert>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-3-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">4</div>
          <div class="title">启用开发者密码</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  点击左侧菜单开发→基本配置，点击启用开发者密码（AppSecret），根据提示进行验证
                </div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-4-1.png`"
                  alt=""
                />
                <img
                  :src="`${baseURL}/statics/system/wechat-2-4-2.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">5</div>
          <div class="title">保存记录AppID、AppSecret</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>本地最好新建一个 txt 文本文档，用于记录相关配置数据</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-5-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">6</div>
          <div class="title">选择是否设置白名单</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>1.关闭白名单，无需做其他配置</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-6-1.png`"
                  alt=""
                />
              </div>
            </div>
            <div class="item">
              <div class="text">
                <div>2.开启白名单，需要同时配置IP白名单</div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-2-6-2.png`"
                  alt=""
                />
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
          <div class="title">登录您的CRMEB系统后台</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  点击设置→应用设置→公众号→基础配置，填写公众号开发者信息，AppID、AppSecret
                </div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-3-1-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">2</div>
          <div class="title">服务器配置（商城后台）</div>
          <div class="content">
            <div class="item">
              <div class="text">
                <div>
                  随机生成
                  EncodingAESKey、微信验证TOKEN，消息加解密方式选择“明文模式”，完成后一定要点击“提交”
                </div>
              </div>
              <div class="image">
                <img
                  :src="`${baseURL}/statics/system/wechat-3-2-1.png`"
                  alt=""
                />
              </div>
            </div>
          </div>
        </TimelineItem>
        <TimelineItem>
          <div class="dot" slot="dot">3</div>
          <div class="title">服务器配置（微信公众平台）</div>
          <div class="content">
            <div v-for="item in list" :key="item" class="item">
              <div v-for="txt in item.text" class="text">
                <div :key="txt" v-html="txt"></div>
              </div>
              <Alert v-if="item.alert.length" show-icon>
                <div>注意事项：</div>
                <div v-for="alt in item.alert">
                  <div v-html="alt"></div>
                </div>
              </Alert>
              <div class="image">
                <img v-for="img in item.image" :src="`${baseURL}/statics/system/${item.image}`" alt="" />
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
  name: 'wechat',
  data() {
    return {
      baseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      list: [
        {
          text: [
            '1.点击左侧菜单 开发→基本配置→服务器配置，点击“修改配置“（如已启动，请先停止）',
          ],
          alert: [],
          image: ['wechat-3-3-1.png'],
        },
        {
          text: [
            '2.将第 2 步商城后台保存提交的配置信息（再次强调上一步需要提交哦~），在微信公众平台上填写。',
          ],
          alert: [
            '1.URL填写内容为http://你的域名/api/Wechat/serve，例如http://<a>www.crmeb.com</a>/api/Wechat/serve',
            '2.AppID、AppSecret、Token、消息加密方式、EncodingAESKey，两边的服务器配置必须要<a href="javascript:;">完全一致</a>哦~',
          ],
          image: ['wechat-3-3-2.png'],
        },
      ],
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
