<!-- 查看章节 -->
<template lang="html">
  <div v-if="isShowModal">
    <div class="modal" v-bind:class="{'is-active': isShowModal}">
      <div class="modal-background"></div>
      <div class="modal-content chapter-content">
        <div class="box chapter-box">
          <div>
            <div class="search-box">
              <input v-model="searchKey" class="input search-input" type="text" placeholder="请输入关键字">
              <!-- <button disabled @click="searchChapter()" class="button" type="button" name="button">查找章节</button> -->
              <div @click="searchChapter()" class="search-button"><i class="fas fa-search"></i></div>
            </div>
              <button @click="addChapter()" class="button add-chapter-button" type="button" name="button">添加章节</button>
          </div>
          <p v-if="!chapterData" class="empty-message-prompt">暂无章节</p>
          <table v-else class="table is-bordered is-striped is-hoverable is-fullwidths">
            <thead>
              <tr>
                <th>ID</th>
                <th>章节名</th>
                <th>描述</th>
                <th>题目类型</th>
                <th>分值</th>
                <th>数量</th>
                <th>题目</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item,index) in chapterData">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td><p class="chapter-limit-words">{{ item.describe }}</p></td>
                <td>{{ computedQuestionType(item.question_type) }}</td>
                <td>{{ item.score }}</td>
                <td>{{ item.number }}</td>
                <td>{{ item.questions }}</td>
                <td>
                  <!-- <button @click="deleteChapter(index)" class="delete" type="button" name="button">删除章节</button> -->
                  <div @click="deleteChapter(index)" class="icon-button"><i class="far fa-trash-alt"></i></div>
                  <button @click="editChapter(index)"  class="button" type="button" name="button">编辑章节</button>
                </td>
              </tr>
            </tbody>
          </table>

          <pagination v-show="searchResult.length === 0"
                      v-bind:pagination-data="paginationData"
                      v-model="data"
          ></pagination>

          <edit-chapter ref="editChapter"
                        v-on:getChapter="getChapter"
                        v-bind:edit-data="editData"
                        v-bind:examination-paper-id="examinationPaperId"
          ></edit-chapter>

          <add-chapter ref="addChapter"
                       v-bind:examination-paper-id="examinationPaperId"
                       v-on:getChapter="getChapter"
          ></add-chapter>
        </div>
      </div>
      <button @click="switchModal()" class="modal-close is-large" aria-label="close"></button>
    </div>
  </div>
</template>

<script>
import Pagination from './../Pagination'
import AddChapter from './AddChapter'
import EditChapter from './EditChapter'

export default {
  data() {
    return {
      isShowModal: false,
      chapterData: null,
      searchKey: null,
      editData: null,  // 当前编辑的章节数据
      paginationData: null,
      data: null,
      examinationPaperId: null,
      // get all chapter
      currentChapter: [],
      allChapter: [],
      searchResult: [],
    }
  },
  components: {
    Pagination,
    AddChapter,
    EditChapter,
  },
  props: [
    'currentExaminationPaperData',
  ],
  methods: {
    switchModal: function () {
      const that = this;
      that.isShowModal = !that.isShowModal;
    },
    addChapter: function() {
      const that = this;
      that.$refs.addChapter.switchModal();
    },
    editChapter: function(index) {
      const that = this;
      that.editData = that.chapterData[index];
      that.$refs.editChapter.switchModal();
    },
    getChapter: function () {
      const that = this;
      let id = that.examinationPaperId;
      axios({
        method: 'get',
        url: `${this.GLOBAL.localDomain}/api/v1/papers/${id}/sections/`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        // ??数据格式有误n
        that.chapterData = res.data.data;
        that.paginationData = res.data.links;
      }).catch(err => {
        console.log(err);
        if (err.response.status === 401) {
          // alert('登录超时');
          // location.reload();
        }
      })
    },
    // searchChapter: function () {
    //   const that = this;
    //   let id = that.searchKey;
    //   axios({
    //     method: 'get',
    //     url: `${this.GLOBAL.localDomain}/api/v1/papers/1/sections/${id}`,
    //     headers: {
    //       'Accept': 'application/json',
    //       'Authorization': sessionStorage.getItem('token'),
    //     }
    //   }).then(res => {
    //     that.chapterData = [];
    //     that.chapterData.push(res.data.data);
    //   }).catch(err => {
    //     console.log(err)
    //   })
    // },
    searchChapter: function () {
      const that = this;
      // 如果没有搜索值
      if (!that.searchKey) {
        that.getChapter();
        that.searchResult = [];
        return;
      }
      // 如果已经获取全部数据
      else if (that.allChapter.length > 0) {
        let allData  = that.allChapter;
        let len = that.allChapter.length;
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
        that.chapterData = res;
      }
      // 如果有搜索值并且还未获取全部数据
      else {
        // let url = `${this.GLOBAL.localDomain}/api/v1/sections/`;
        let url = `${this.GLOBAL.localDomain}/api/v1/papers/${that.examinationPaperId}/sections/`;
        that.getAllChapter(url);
      }
    },
    getAllChapter: function (url) {
      const that = this;
      let urlPath = url ? url : that.url
      axios({
        method: 'get',
        url: urlPath,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        that.url = res.data.links.next;

        let len = res.data.data.length ? res.data.data.length : that.getJsonLength(res.data.data);

        // data数据结构不一致 可能是数组/也可能是json
        if (res.data.data.length) {
          for (let i = 0; i < len; i++) {
            that.currentChapter.push(res.data.data[i]);
          }
        }
        else if (that.getJsonLength(res.data.data)) {
          for (let i in res.data.data) {
            that.currentChapter.push(res.data.data[i]);
          }
        }

        if (that.url) {
          that.getAllChapter(that.url);
        }
        else {
          that.allChapter = that.currentChapter;
        }
      }).catch(err => {
        console.log(err);
      })
    },
    deleteChapter: function (index) {
      const that = this;
      let chapterId = that.chapterData[index]['id'];
      let paperId = that.examinationPaperId;
      let prompt = confirm("确认删除该章节吗？");

      if (prompt) {
        axios({
          method: 'delete',
          url: `${this.GLOBAL.localDomain}/api/v1/papers/${paperId}/sections/${chapterId}`,
          headers: {
            'Accept': 'application/json',
            'Authorization': sessionStorage.getItem('token'),
          }
        }).then(res => {
          alert('删除成功')
          that.getChapter();
        }).catch(err => {
          alert('删除失败')
          console.log(err)
        })
      }
    },
    computedQuestionType: function (value) {
      const that = this;

      // 1.SINGLE_CHOICE, 2.MULTIPLE_CHOICE, 3.TRUE_FALSE, 4.FILL_IN, 5.SHORT_ANSWER

      let questionType = '';
      switch (value) {
        case 1:
          questionType = '单选';
          break;
        case 2:
          questionType = '多选';
          break;
        case 3:
          questionType = '判断';
          break;
        case 4:
          questionType = '填空';
          break;
        case 5:
          questionType = '简答';
          break;
      }
      return questionType;
    }
  },
  created() {
  },
  watch: {
    // data:function (value, oldValue) {
    //   const that = this;
    //   that.chapterData = value.data;
    //   that.paginationData = value.links;
    // },
    currentExaminationPaperData: function (value, oldValue) {
      const that = this;
      that.examinationPaperId = value.id;
      this.getChapter();
    },
    allChapter: function (value, oldValue) {
      const that = this;
      that.searchChapter(that.searchKey);
    }
  }
}
</script>

<style lang="scss">
table {
  margin: 35px auto 0 auto;
}
.search-input {
  width: 200px;
  display: inline-block;
  margin-right: 10px;
}
.search-box {
  padding-right: 20px;
  display: inline-block;
  border-right: 1px solid #dedede;
}
.add-chapter-button {
  margin-left: 20px;
}
.chapter-content {
  width: 1200px;
}
.chapter-limit-words {
  display: inline-block;
  width: 140px;
  overflow: hidden;
  text-overflow:ellipsis;
  white-space: nowrap;
}
</style>
