<template>
  <v-card>
    <v-card-title>
      Posts
      <v-spacer></v-spacer>
      <v-btn color="primary" @click="syncPosts">Sync from WordPress</v-btn>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="posts"
      :items-per-page="10"
      class="elevation-1"
    >
      <template #item.priority="{ item }">
        <v-text-field v-model="item.priority" type="number" @change="updatePriority(item)"></v-text-field>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      posts: [],
      headers: [
        { text: 'Title', value: 'title' },
        { text: 'Status', value: 'status' },
        { text: 'Created At', value: 'wp_created_at' },
        { text: 'Priority', value: 'priority' },
      ]
    }
  },
  mounted() {
    this.fetchPosts();
  },
  methods: {
    fetchPosts() {
      axios.get('/posts').then(res => {
        this.posts = res.data.posts;
      });
    },
    syncPosts() {
      axios.post('/posts/sync').then(() => {
        this.fetchPosts();
      });
    },
    updatePriority(post) {
      axios.patch(`/posts/${post.id}/priority`, { priority: post.priority });
    }
  }
}
</script>
