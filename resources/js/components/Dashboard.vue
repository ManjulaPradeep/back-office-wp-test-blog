<template>
    <v-app>
        <v-main>
            <v-container>
                <v-row>
                    <v-col>
                        <h1>Welcome, {{ user.name }}</h1>
                    </v-col>
                    <v-col class="text-end">
                        <v-btn color="error" @click="logout">Logout</v-btn>
                    </v-col>
                </v-row>
                <posts-table></posts-table>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import PostsTable from './PostsTable.vue';
import axios from 'axios';

export default {
    props: ['user'],
    components: { PostsTable },
    methods: {
        logout() {
            axios.post('/auth/logout', {}, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                window.location = '/auth/login';
            });
        }
    }
}
</script>
