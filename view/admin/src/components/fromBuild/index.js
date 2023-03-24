//注册组件
export default {
    components:{
        cardBuild: () => import('./cardBuild'),
        inputBuild: () => import('./inputBuild'),
        inputNumberBuild: () => import('./inputNumberBuild'),
        radioBuild: () => import('./radioBuild'),
        selectBuild: () => import('./selectBuild'),
        switchBuild: () => import('./switchBuild'),
        uploadImageBuild: () => import('./uploadImageBuild'),
        uploadFrameBuild: () => import('./uploadFrameBuild'),
        tabsBuild: () => import('./tabsBuild'),
        alertBuild: () => import('./alertBuild'),
        diyTableBuild: () => import('./diyTableBuild'),
        timeBuild:() => import('./timeBuild'), // 时间选择
        addressBuild:() => import('./addressBuild.vue'), // 地址选择
        mapBuild:() => import ('./mapBuild.vue') // 地图选择
       
    },
}
