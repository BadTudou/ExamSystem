<template lang="html">
  <div class="modal" :class="{'is-active' : isShowModal}">
    <div class="modal-background"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">添加考试</p>
        <button @click="switchModal()" class="delete" aria-label="close"></button>
      </header>
      <section class="modal-card-body">
        <div class="box-item">
          <label>考试标题</label>
          <input v-model="testData.title" class="input" type="text">
        </div>
        <!-- <div class="box-item">
          <label>类型</label>
          <div class="select">
            <select v-model="testData.exam_type">
              <option value="OPEN">开卷</option>
            </select>
          </div>
        </div> -->
        <div class="box-item">
          <label>描述</label>
          <input v-model="testData.describe" class="input" type="text">
        </div>
        <div class="box-item">
          <label>总分数</label>
          <input v-model="testData.score" class="input" type="text">
        </div>
        <div class="box-item">
          <label>考试时长(分钟)</label>
          <input v-model="testData.min" class="input" type="text">
        </div>
        <div class="box-item">
          <label>开始时间</label>
          <input v-model="testData.begin_at" class="input" type="date">
        </div>
        <div class="box-item">
          <label>相关的试卷ID</label>
          <!-- <input v-model="testData.paper_id" class="input" type="text"> -->
          <div class="all-question">
            <table class="table">
              <thead>
                <tr>
                  <th>是否选中</th>
                  <th>序号</th>
                  <th>名称</th>
                  <th>考试类型</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,index) in examinationPaperData">
                  <td><input type="radio" v-bind:value="item.id" v-model="selectedExaminationPaper" class="test-selected"></td>
                  <td>{{ item.id }}</td>
                  <td>{{ item.title }}</td>
                  <td>{{ item.exam_type }}</td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button @click="addTest()" class="button is-success">确认</button>
        <button @click="switchModal()" class="button">取消</button>
      </footer>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isShowModal: false,
      testData: {
        title: '',
        describe: '',
        score: '',
        min: '',
        begin_at: '',
        paper_id: ''
      },
      examinationPaperData: {},
      selectedExaminationPaper: '',
    }
  },
  components: {
  },
  methods: {
    switchModal: function () {
      const that = this;
      that.isShowModal = !that.isShowModal;
    },
    clearWords: function () {
      const that = this;
      that.testData.title =  '';
      that.testData.describe =  '';
      that.testData.score =  '';
      that.testData.min =  '';
      that.testData.begin_at =  '';
      that.testData.paper_id =  '';
    },
    addTest: function () {
      const that = this;
      if (!that.testData.title || !that.testData.describe || !that.testData.score ||
          !that.testData.min   || !that.testData.begin_at || !that.selectedExaminationPaper) {
        alert('请确认内容是否填写完整')
      }
      axios({
        method: 'post',
        url: `${this.GLOBAL.localDomain}/api/v1/exams`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        },
        params: {
          title: that.testData.title,
          exam_type: 'EXAM',
          describe: that.testData.describe,
          score: that.testData.score,
          min: that.testData.min,
          begin_at: that.testData.begin_at,
          paper_id: that.selectedExaminationPaper,
        }
      }).then(res => {
        alert('添加成功');
        that.clearWords();
        that.$emit('getTest');   //第一个参数名为调用的方法名，第二个参数为需要传递的参数
        that.switchModal();
      }).catch(err => {
        let errMsg = err.response.data.message;
        if (errMsg) {
          alert(errMsg);
        }
        else {
          alert('添加失败');
        }
        console.log(err);
      })
    },
    getExaminationPaper: function () {
      const that = this;
      axios({
        method: 'get',
        url: `${this.GLOBAL.localDomain}/api/v1/papers`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        that.examinationPaperData = res.data.data;
        that.paginationData = res.data.links;
      }).catch(err => {
        console.log(err)
      })
    },
  },
  created() {
  },
  watch: {
    isShowModal: function (value, oldVale) {
      const that = this;
      if (value) {
        this.getExaminationPaper();
      }
    },
  }
}
</script>

<style scoped>
.test-selected {
  width: 20px;
}
</style>
