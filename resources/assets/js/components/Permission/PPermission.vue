<!-- 查看权限 -->
<template lang="html">
  <div class="box">
    <div>
      <div v-show="isShowSearchPermission" class="search-box">
        <input v-model="searchKey" class="input search-input" type="text" placeholder="请输入关键字">
        <div @click="searchPermission()" class="search-button"><i class="fas fa-search"></i></div>
      </div>
        <button v-show="isShowCreatePermission" @click="addPermission()" class="button add-permission-button" type="button" name="button">添加权限</button>
    </div>

    <p v-if="!permissionData" class="empty-message-prompt">暂无权限</p>
    <table v-else class="table is-bordered is-striped is-hoverable is-fullwidths">
      <thead>
        <tr>
          <th>ID</th>
          <th>权限名</th>
          <th>别名</th>
          <th>描述</th>
          <th>创建时间</th>
          <!-- <th>更新时间</th> -->
          <th v-show="isShowDeletePermission">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item,index) in permissionData">
          <td>{{ item.id }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.display_name }}</td>
          <td><p class="limit-words">{{ item.description }}</p></td>
          <td>{{ GLOBAL.toTime(item.created_at) }}</td>
          <!-- <td>{{ GLOBAL.toTime(item.updated_at) }}</td> -->
          <td><div v-show="isShowDeletePermission" @click="deletePermission(index)" class="icon-button"><i class="far fa-trash-alt"></i></div></td>
        <!-- <td><button v-show="isShowDeletePermission" @click="deletePermission(index)" class="delete" type="button" name="button">删除权限</button></td> -->
        </tr>
      </tbody>
    </table>

    <pagination v-show="searchResult.length === 0"
                v-bind:pagination-data="paginationData"
                v-model="data"
    ></pagination>

    <add-permission ref="addPermission"
                    v-on:getPermission="getPermission"
    ></add-permission>

  </div>
</template>

<script>
import AddPermission from './AddPermission'
import Pagination from './../Pagination.vue'
export default {
  data() {
    return {
      permissionData: null,
      isShowModal: false,
      searchKey: null,
      // pagination
      paginationData: null,
      data: null,
      //

      // get all permission
      currentPermission: [],
      allPermission: [],
      searchResult: [],
    }
  },
  components: {
    AddPermission,
    Pagination,
  },
  methods: {
    showModal: function () {
      const that = this;
      that.isShowModal = !that.isShowModal;
    },
    addPermission: function () {
      const that = this;
      that.$refs.addPermission.switchModal();
    },
    deletePermission: function (index) {
      const that = this;
      let id = that.permissionData[index]['id'];
      let prompt = confirm("确认删除该权限吗？");
      if (prompt) {
        axios({
          method: 'delete',
          url: `${this.GLOBAL.localDomain}/api/v1/permissions/${id}`,
          headers: {
            'Accept': 'application/json',
            'Authorization': sessionStorage.getItem('token')
          }
        }).then(res => {
          alert('删除成功！');
          that.getPermission();
        }).catch(err => {
          alert('删除失败，请稍后再试')
          console.log(err)
        })
      }
    },
    getPermission: function (page = 1) {
      const that = this;
      axios({
        method: 'get',
        url: `${this.GLOBAL.localDomain}/api/v1/permissions/`,
        headers: {
          'Accept': 'application/json',
          'Authorization': sessionStorage.getItem('token'),
        }
      }).then(res => {
        that.permissionData = res.data.data;
        that.paginationData = res.data.links;
      }).catch(res => {
        console.log(err)
        if (err.response.status === 401) {
          // alert('登录超时');
          // location.reload();
        }
      })
    },
    // searchPermission: function () {
    //   const that = this;
    //   // 如果没有搜索值
    //   if (!that.searchKey) {
    //     that.getPermission();
    //     that.searchResult = [];
    //     return;
    //   }
    //   axios({
    //     method: 'get',
    //     url: `${this.GLOBAL.localDomain}/api/v1/permissions/${that.searchKey}`,
    //     headers: {
    //       'Accept': 'application/json',
    //       'Authorization': sessionStorage.getItem('token')
    //     }
    //   }).then(res => {
    //     that.permissionData = [];
    //     that.permissionData.push(res.data.data);
    //   }).catch(err => {
    //     console.log(err)
    //   })
    // },
    searchPermission: function () {
      const that = this;
      // 如果没有搜索值
      if (!that.searchKey) {
        that.getPermission();
        that.searchResult = [];
        return;
      }
      // 如果已经获取全部数据
      else if (that.allPermission.length > 0) {
        let allData  = that.allPermission;
        let len = that.allPermission.length;
        let res = [];

        for (let i = 0; i < len; i++) {
          if (allData[i][j]) {
            for (let j in allData[i]) {
              if ((allData[i][j].toString()).indexOf(that.searchKey) !== -1) {
                res.push(allData[i]);
                break;
              }
            }
          }
        }
        that.searchResult = res;
        that.permissionData = res;
      }
      // 如果有搜索值并且还未获取全部数据
      else {
        let url = `${this.GLOBAL.localDomain}/api/v1/permissions/`;
        that.getAllPermission(url);
      }
    },
    getAllPermission: function (url) {
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
            that.currentPermission.push(res.data.data[i]);
          }
        }
        else if (that.getJsonLength(res.data.data)) {
          for (let i in res.data.data) {
            that.currentPermission.push(res.data.data[i]);
          }
        }

        if (that.url) {
          that.getAllPermission(that.url);
        }
        else {
          that.allPermission = that.currentPermission;
        }
      }).catch(err => {
        console.log(err);
      })
    },
    getJsonLength: function (json) {
      const that = this;
      let jsonLength = 0;
      for (let i in json) {
          jsonLength++;
      }
      return jsonLength;
    },
  },
  computed: {
    isShowCreatePermission() {
      // return true;
      return sessionStorage.getItem('permissions').includes('permission-store');
    },
    isShowSearchPermission() {
      // return true;
      return sessionStorage.getItem('permissions').includes('permission-show')
    },
    isShowDeletePermission() {
      // return true;
      return sessionStorage.getItem('permissions').includes('permission-destroy')
    },
  },
  created() {
    this.permissionData = [];
    this.getPermission();
  },
  watch: {
    data:function (value, oldValue) {
      const that = this;
      that.permissionData = value.data;
      that.paginationData = value.links;
    },
    allPermission: function (value, oldValue) {
      const that = this;
      that.searchPermission(that.searchKey);
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
.add-permission-button {
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
</style>
