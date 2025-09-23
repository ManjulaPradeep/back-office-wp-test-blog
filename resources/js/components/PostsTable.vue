<template>
    <v-card>
        <v-card-title>
            Posts
            <v-spacer></v-spacer>
            <v-btn color="info" :loading="syncLoading" :disabled="loading" @click="syncPosts">
                <v-icon left>mdi-sync</v-icon>
                Sync from WordPress
            </v-btn>
            <v-btn color="success" class="ml-2" :disabled="loading" @click="openDialog()">
                <v-icon left>mdi-plus</v-icon>
                New Post
            </v-btn>
        </v-card-title>

        <!-- Loading overlay for the entire card -->
        <v-overlay v-if="loading" contained class="align-center justify-center">
            <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
            <div class="text-center mt-4">
                <p>{{ loadingMessage }}</p>
            </div>
        </v-overlay>

        <v-data-table :headers="headers" :items="posts" :items-per-page="10" :loading="tableLoading"
            loading-text="Loading posts..." class="elevation-1">
            <!-- Priority column with inline editing -->
            <template #item.priority="{ item }">
                <div class="d-flex align-center">
                    <v-text-field v-model.number="item.priority" type="number" min="0" max="100" density="compact"
                        variant="outlined" hide-details style="width: 80px;" :loading="item.priorityUpdating"
                        @blur="updatePriority(item)" @keyup.enter="updatePriority(item)"></v-text-field>
                    <v-progress-circular v-if="item.priorityUpdating" indeterminate size="20"
                        class="ml-2"></v-progress-circular>
                </div>
            </template>

            <template #item.content="{ item }">
                <div class="truncate-content">
                    {{ truncateContent(item.content) }}
                </div>
            </template>

            <template #item.status="{ item }">
                <v-chip :color="getStatusColor(item.status)" size="small" variant="tonal">
                    {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
                </v-chip>
            </template>

            <template #item.wp_created_at="{ item }">
                <span v-if="item.wp_created_at">
                    {{ formatDate(item.wp_created_at) }}
                </span>
                <span v-else class="text-grey">
                    {{ formatDate(item.created_at) }}
                </span>
            </template>

            <template #item.actions="{ item }">
                <v-btn icon="mdi-pencil" color="blue" size="small" variant="text" :disabled="item.updating || loading"
                    @click="openDialog(item)">

                </v-btn>
                <v-btn icon="mdi-delete" color="red" size="small" variant="text" :loading="item.deleting"
                    :disabled="loading" @click="confirmDelete(item)">
                </v-btn>
            </template>

            <template #no-data>
                <div class="text-center pa-8">
                    <v-icon size="64" color="grey-lighten-2">mdi-post-outline</v-icon>
                    <h3 class="text-h6 mt-4 mb-2">No posts found</h3>
                    <p class="text-body-2 mb-4">Create your first blog post to get started.</p>
                    <v-btn color="primary" @click="openDialog()">
                        <v-icon left>mdi-plus</v-icon>
                        Create Post
                    </v-btn>
                </div>
            </template>
        </v-data-table>

        <!-- Create / Edit Dialog -->
        <v-dialog v-model="dialog" max-width="800px" persistent>
            <v-card>
                <v-card-title>
                    <span class="text-h5">
                        {{ editedPost.id ? 'Edit Post' : 'New Post' }}
                    </span>
                </v-card-title>

                <v-card-text>
                    <v-form ref="form" lazy-validation>
                        <v-text-field v-model="editedPost.title" label="Title *" :rules="titleRules"
                            :loading="formLoading" required variant="outlined" class="mb-4"></v-text-field>

                        <v-textarea v-model="editedPost.content" label="Content" :loading="formLoading"
                            variant="outlined" auto-grow rows="8" class="mb-4"></v-textarea>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select v-model="editedPost.status" :items="statusOptions" label="Status *"
                                    :loading="formLoading" required variant="outlined"></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model.number="editedPost.priority" label="Priority (0-100)"
                                    type="number" min="0" max="100" :loading="formLoading" variant="outlined"
                                    hint="Higher numbers = higher priority" persistent-hint></v-text-field>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text :disabled="formLoading" @click="closeDialog">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" :loading="formLoading" @click="savePost">
                        {{ editedPost.id ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="400px">
            <v-card>
                <v-card-title class="text-h5">
                    Confirm Delete
                </v-card-title>
                <v-card-text>
                    Are you sure you want to delete "{{ postToDelete?.title }}"?
                    <br><br>
                    <v-alert type="warning" variant="tonal">
                        This will permanently delete the post from both the back-office and WordPress.
                    </v-alert>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text :disabled="deleteLoading" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="red" :loading="deleteLoading" @click="deletePost">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Snackbar for notifications -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="snackbar.timeout" location="top right">
            {{ snackbar.message }}
            <template v-slot:actions>
                <v-btn variant="text" @click="snackbar.show = false">
                    Close
                </v-btn>
            </template>
        </v-snackbar>
    </v-card>
</template>

<script>
import axios from 'axios';

export default {
    name: 'PostsTable',
    data() {
        return {
            // Posts data
            posts: [],

            // Loading states
            loading: false,
            tableLoading: false,
            syncLoading: false,
            formLoading: false,
            deleteLoading: false,
            loadingMessage: 'Loading posts...',

            // Dialog states
            dialog: false,
            deleteDialog: false,

            // Form data
            editedPost: {},
            postToDelete: null,

            // Validation rules
            titleRules: [
                v => !!v || 'Title is required',
                v => (v && v.length >= 3) || 'Title must be at least 3 characters',
            ],

            // Options
            statusOptions: [
                { title: 'Published', value: 'publish' },
                { title: 'Draft', value: 'draft' },
                { title: 'Private', value: 'private' }
            ],

            // Table configuration
            headers: [
                {
                    title: 'Title',
                    value: 'title',
                    sortable: true
                },
                {
                    title: 'Content',
                    value: 'content',
                    sortable: false,
                    width: '300px'
                },
                {
                    title: 'Status',
                    value: 'status',
                    sortable: true,
                    width: '120px'
                },
                {
                    title: 'Created At',
                    value: 'wp_created_at',
                    sortable: true,
                    width: '150px'
                },
                {
                    title: 'Priority',
                    value: 'priority',
                    sortable: true,
                    width: '120px'
                },
                {
                    title: 'Actions',
                    value: 'actions',
                    sortable: false,
                    width: '100px'
                },
            ],

            // Notifications
            snackbar: {
                show: false,
                message: '',
                color: 'success',
                timeout: 4000
            }
        };
    },

    mounted() {
        this.fetchPosts();
    },

    methods: {
        async fetchPosts() {
            this.tableLoading = true;
            try {
                const response = await axios.get('/posts');

                // Handle different response structures
                if (response.data.posts) {
                    this.posts = response.data.posts;
                } else if (Array.isArray(response.data)) {
                    this.posts = response.data;
                } else {
                    this.posts = [];
                }

                // Add reactive properties for individual loading states
                this.posts = this.posts.map(post => ({
                    ...post,
                    priorityUpdating: false,
                    updating: false,
                    deleting: false
                }));

                this.showSuccess(`Loaded ${this.posts.length} posts successfully`);
            } catch (error) {
                console.error('Fetch posts error:', error);
                this.showError('Failed to fetch posts');
                this.posts = [];
            } finally {
                this.tableLoading = false;
            }
        },

        async syncPosts() {
            this.syncLoading = true;
            this.loadingMessage = 'Syncing posts from WordPress...';

            try {
                const response = await axios.post('/posts/sync');

                // Refresh posts after sync
                await this.fetchPosts();

                if (response.data.message) {
                    this.showSuccess(response.data.message);
                } else {
                    this.showSuccess('Posts synced successfully');
                }
            } catch (error) {
                console.error('Sync error:', error);
                this.showError('Failed to sync posts from WordPress');
            } finally {
                this.syncLoading = false;
            }
        },

        async updatePriority(post) {
            // Prevent multiple simultaneous updates
            if (post.priorityUpdating) return;

            post.priorityUpdating = true;

            try {
                const response = await axios.patch(`/posts/${post.id}/priority`, {
                    priority: post.priority || 0
                });

                // Update the post with response data if available
                if (response.data) {
                    Object.assign(post, response.data);
                }

                this.showSuccess('Priority updated successfully');
            } catch (error) {
                console.error('Priority update error:', error);
                this.showError('Failed to update priority');

                // Revert the priority value on error
                await this.fetchPosts();
            } finally {
                post.priorityUpdating = false;
            }
        },

        openDialog(post = null) {
            if (post) {
                // Edit mode - clone the post data
                this.editedPost = {
                    ...post,
                    priority: post.priority || 0
                };
            } else {
                // Create mode - empty form
                this.editedPost = {
                    title: '',
                    content: '',
                    status: 'draft',
                    priority: 0
                };
            }
            this.dialog = true;
        },

        closeDialog() {
            this.dialog = false;
            this.editedPost = {};

            // Reset form validation
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },

        async savePost() {
            // Validate form
            if (!this.$refs.form.validate()) {
                this.showError('Please fix the form errors');
                return;
            }

            this.formLoading = true;
            const isEdit = !!this.editedPost.id;

            try {
                let response;

                if (isEdit) {
                    response = await axios.put(`/posts/${this.editedPost.id}`, this.editedPost);

                    // Update the post in the list
                    const index = this.posts.findIndex(p => p.id === response.data.id);
                    if (index !== -1) {
                        // Preserve loading states
                        const loadingStates = {
                            priorityUpdating: this.posts[index].priorityUpdating,
                            updating: this.posts[index].updating,
                            deleting: this.posts[index].deleting
                        };

                        this.posts.splice(index, 1, {
                            ...response.data,
                            ...loadingStates
                        });
                    }

                    this.showSuccess('Post updated successfully');
                } else {
                    response = await axios.post('/posts', this.editedPost);

                    // Add new post to the beginning of the list
                    this.posts.unshift({
                        ...response.data,
                        priorityUpdating: false,
                        updating: false,
                        deleting: false
                    });

                    this.showSuccess('Post created successfully');
                }

                this.closeDialog();
            } catch (error) {
                console.error('Save post error:', error);

                let errorMessage = 'An error occurred';
                if (error.response?.data?.error) {
                    errorMessage = error.response.data.error;
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.message) {
                    errorMessage = error.message;
                }

                this.showError(isEdit ? `Failed to update post: ${errorMessage}` : `Failed to create post: ${errorMessage}`);
            } finally {
                this.formLoading = false;
            }
        },

        confirmDelete(post) {
            this.postToDelete = post;
            this.deleteDialog = true;
        },

        async deletePost() {
            if (!this.postToDelete) return;

            this.deleteLoading = true;

            try {
                await axios.delete(`/posts/${this.postToDelete.id}`);

                // Remove post from list
                this.posts = this.posts.filter(p => p.id !== this.postToDelete.id);

                this.showSuccess('Post deleted successfully');
                this.deleteDialog = false;
                this.postToDelete = null;
            } catch (error) {
                console.error('Delete error:', error);

                let errorMessage = 'An error occurred';
                if (error.response?.data?.error) {
                    errorMessage = error.response.data.error;
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }

                this.showError(`Failed to delete post: ${errorMessage}`);
            } finally {
                this.deleteLoading = false;
            }
        },

        // Utility methods
        getStatusColor(status) {
            switch (status) {
                case 'publish': return 'success';
                case 'draft': return 'warning';
                case 'private': return 'info';
                default: return 'grey';
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';

            try {
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (error) {
                return 'Invalid date';
            }
        },

        truncateContent(content) {
            if (!content) return '—';
            const text = content.replace(/<\/?[^>]+(>|$)/g, "");
            return text.length > 120 ? text.substring(0, 120) + "..." : text;
        },

        // Notification methods
        showSuccess(message) {
            this.showSnackbar(message, 'success');
        },

        showError(message) {
            this.showSnackbar(message, 'error', 6000);
        },

        showWarning(message) {
            this.showSnackbar(message, 'warning');
        },

        showInfo(message) {
            this.showSnackbar(message, 'info');
        },

        showSnackbar(message, color = 'success', timeout = 4000) {
            this.snackbar = {
                show: true,
                message,
                color,
                timeout
            };
        }
    }
};
</script>

<style scoped>
/* Add any custom styles here */
.v-data-table {
    background-color: transparent;
}

/* Ensure priority input doesn't cause layout shifts */
.v-text-field input {
    text-align: center;
}

/* Custom loading overlay styles */
.v-overlay .text-center p {
    color: rgba(0, 0, 0, 0.6);
    margin-top: 16px;
    font-weight: 500;
}
</style>
