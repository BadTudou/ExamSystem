<!-- 查看视频 -->
<template lang="html">
  <div class="modal" :class="{'is-active' : isShowModal}">
    <div class="modal-background"></div>
    <div class="modal-content">
      <div v-if="!isWatching" class="box">

        <div class="search-box">
          <div class="field">
            <div class="control">
              <div class="select is-small">
                <select  v-model="searchType" @change="changeSeachType()">
                  <option value="">请选择查找类型</option>
                  <option value="fuzzy-search">模糊查找</option>
                  <option value="user-id-search">根据用户ID查找</option>
                  <option value="lecture-id-search">根据授课ID查找</option>
                </select>
              </div>
            </div>
          </div>

          <input v-model="searchKey" class="input search-input" type="text" placeholder="">
          <div class="search-button"><i class="fas fa-search"></i></div>
        </div>

        <button v-show="roleName !== 'student'" @click="addVideoInfo()" class="button add-video-button" type="button" name="button">添加视频</button>

        <p v-if="!videoData || videoData.length === 0" class="empty-message-prompt">暂无视频</p>
        <table v-else class="table is-bordered is-striped is-hoverable is-fullwidths">
          <thead>
            <!-- "id": 15,
            "userid": 1,
            "cid": 1,
            "url": "\/usr\/share\/nginx\/html\/ExamSystem\/public\/video\/123.mp4",
            "video_name": "\u6d4b\u8bd5\u89c6\u9891",
            "kp": "\u77e5\u8bc6\u70b9\u662fxxxx" -->
            <tr>
              <th>序号</th>
              <th>用户ID</th>
              <th>授课ID</th>
              <th>URL</th>
              <th>视频名</th>
              <th>知识点</th>
              <th>操作</th>
            </tr>
          </thead>
        <tbody>
            <tr v-for="(item,index) in videoData">
              <td>{{ item.id }}</td>
              <td>{{ item.userId }}</td>
              <td>{{ item.cid }}</td>
              <td>{{ GLOBAL.localDomain + item.url }}</td>
              <td>{{ item.video_name }}</td>
              <td>{{ item.kp }}</td>
              <td>
                <div v-show="roleName !== 'student'" @click="deleteVideo(index)" class="icon-button"><i class="far fa-trash-alt"></i></div>
                <div v-show="roleName !== 'student'" @click="editVideoInfo(index)" class="icon-button"><i class="fas fa-edit"></i></div>
                <button @click="showVideo(index)" class="button is-small" type="button" name="button">观看视频</button>
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <div v-else class="box video-show">
        <video width="700" height="450" controls autoplay>
          <source v-show="currentVideoType === 'mp4'"  :src="currentVideoData.url" type="video/mp4">
          <source v-show="currentVideoType === 'ogg'"  :src="currentVideoData.url" type="video/ogg">
          <source v-show="currentVideoType === 'webm'" :src="currentVideoData.url" type="video/webm">
        </video>
      </div>
    </div>
    <button @click="switchModal()" class="modal-close is-large" aria-label="close"></button>


    <add-video-info v-if="isShowCreateVideo"
                    ref="addVideoInfo"
                    v-on:getVideo="getVideo"
                    v-bind:current-teaching-data="currentTeachingData"></add-video-info>

    <edit-video-info v-if="isShowEditVideo"
                     ref="editVideoInfo"
                     v-on:getVideoForCid="getVideoForCid"
                     v-bind:edit-data="editData"
    ></edit-video-info>

  </div>

</template>

<script>
import AddVideoInfo from './AddVideoInfo'
import EditVideoInfo from './EditVideoInfo'

export default {
  data() {
    return {
      isShowModal: false,
      videoData: null,
      searchKey: '',
      // get all video
      currentVideo: [],
      allVideo: [],
      searchResult: [],
      editData: null,
      searchType: '',
      currentVideoData: null,
      isWatching: false,
      currentVideoType: '',
      roleName: sessionStorage.getItem('roleName'),
    }
  },
  components: {
    AddVideoInfo,
    EditVideoInfo,
  },
  props: [
    'currentTeachingData'
  ],
  methods: {
    switchModal: function () {
      const that = this;
      that.isShowModal = !that.isShowModal;
    },
    addVideoInfo: function() {
      const that = this;
      that.$refs.addVideoInfo.switchModal();
    },
    editVideoInfo: function (index) {
      const that = this;
      that.editData = that.videoData[index];
      that.$refs.editVideoInfo.switchModal();
    },
    deleteVideo: function (index) {
      const that = this;
      let id = that.videoData[index]['id'];
      let prompt = confirm("确认删除该视频吗？");
      if (prompt) {
        axios({
          method: 'post',
          url: `${this.GLOBAL.localDomain}/api/v1/upload/lecture/delete/video`,
          headers: {
            'Accept': 'application/json',
            'Authorization': sessionStorage.getItem('token'),
          },
          params: {
            id: that.videoData[index]['id']
          }
        }).then(res => {
          alert('删除成功');
          that.getVideo()
        }).catch(err => {
          alert('删除失败')
          console.log(err)
        })
      }
    },
    // 通过授课ID获取视频信息
    getVideoForCid: function() {
      const that = this;
      let cid = that.currentTeachingData.id;
      axios({
        method: 'post',
        url: `${this.GLOBAL.localDomain}/api/v1/upload/lecture/selectForCid/video`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        },
        params: {
          'cid': cid
        },
      }).then(res => {
        that.videoData = res.data;
      }).catch(err => {
        console.log(err);
      })
    },
    // 通过user获取视频信息
    getVideoForUserId: function() {
      const that = this;
      axios({
        method: 'post',
        url: `${this.GLOBAL.localDomain}/api/v1/upload/lecture/selectForUserid/video`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        },
        params: {
          userId: sessionStorage.getItem('userId')
        }
      }).then(res => {
        that.videoData = res.data;
        // that.paginationData = res.data.links;
      }).catch(err => {
        console.log(err);
      })
    },
    getVideo: function() {
      const that = this;
      axios({
        method: 'post',
        url: `${this.GLOBAL.localDomain}/api/v1/upload/lecture/selectAll/video`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        that.videoData = res.data;

        that.paginationData = res.data.links;
        //
      }).catch(err => {
        console.log(err);
      })
    },
    searchVideo: function () {
      const that = this;
      // 如果没有搜索值
      if (!that.searchKey) {
        that.getVideo();
        that.searchResult = [];
        return;
      }
      // 如果已经获取全部数据
      else if (that.allVideo.length > 0) {
        let allData  = that.allVideo;
        let len = that.allVideo.length;
        let res = [];

        for (let i = 0; i < len; i++) {
          for (let j in allData[i]) {
            if (allData[i][j]) {
              if ((allData[i][j].toString()).indexOf(that.searchKey) !== -1) {
                res.push(allData[i]);
                break;
              }
            }
          }
        }
        that.searchResult = res;
        that.videoData = res;
      }
      // 如果有搜索值并且还未获取全部数据
      else {
        let url = `${this.GLOBAL.localDomain}/api/v1/videos/`;
        that.getAllVideo(url);
      }
    },
    getAllVideo: function (url) {
      const that = this;
      let urlPath = url ? url : that.url
      axios({
        method: 'post',
        url: urlPath,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        that.url = res.data.links.next;

        let len = res.data.length ? res.data.length : that.getJsonLength(res.data);

        // data数据结构不一致 可能是数组/也可能是json
        if (res.data.length) {
          for (let i = 0; i < len; i++) {
            that.currentVideo.push(res.data[i]);
          }
        }
        else if (that.getJsonLength(res.data)) {
          for (let i in res.data) {
            that.currentVideo.push(res.data[i]);
          }
        }

        if (that.url) {
          that.getAllVideo(that.url);
        }
        else {
          that.allVideo = that.currentVideo;
        }
      }).catch(err => {
        console.log(err);
      })
    },
    changeSeachType: function () {
      const that = this;
      let searchType = that.searchType;
      switch (searchType) {
        case '':
          break;
        case 'fuzzy-search':
          that.getVideo();
          break;
        case 'user-id-search':
          that.getVideoForUserId();
          break;
        case 'lecture-id-search':
          that.getVideoForCid();
          break;
      }
    },
    showVideo: function (index) {
      const that = this;

      that.currentVideoData = that.videoData[index];
      that.isWatching = true;

    },
    getVideoType: function() {
      const that = this;
      let url = that.currentVideoData.url;
      this.currentVideoType = url.split('.')[1];
      return type;
    }
  },
  computed: {
    isShowCreateVideo() {
      // return true;
      return sessionStorage.getItem('permissions').includes('video-store');
    },
    // isShowSearchVideo() {
    //   // return true;
    //   return sessionStorage.getItem('permissions').includes('question-show');
    // },
    isShowEditVideo() {
      // return true;
      return sessionStorage.getItem('permissions').includes('video-update');
    },
    isShowDeleteVideo() {
      // return true;
      return sessionStorage.getItem('permissions').includes('video-destroy');
    }
  },
  created() {
  },
  watch: {
    isShowModal: function (value, oldValue) {
      const that = this;
      if (value) {
        that.isWatching = false;
        that.getVideo();
      }

    },
    currentTeachingData: function (value, oldValue) {
      const that = this;
      that.getVideo();
    }
  }
}
</script>

// <style lang="scss" scoped>
table {
  margin: 35px auto 0 auto;
}
.search-input {
  width: 200px;
  display: inline-block;
  margin-right: 10px;
}
.search-box {
  width: 400px;
  padding-right: 20px;
  display: inline-block;
  border-right: 1px solid #dedede;
}
.add-video-button {
  margin-left: 20px;
}
.box-item {
  margin-bottom: 20px;
  input  {
    display: inline-block;
    width: 300px;
  }
  label {
    display: inline-block;
    width: 130px;
  }
}
.modal-content {
  width: 1200px;
}
.field {
  display: inline-block;
}
video {
  margin: 0 auto;
  display: block;
}
</style>
