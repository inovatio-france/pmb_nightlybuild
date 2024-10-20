<template>
    <div id="ftp-form">
        <settings-form :settings="currentType.settings" :msg="currentType.msg" :form-values="formValues"></settings-form>
        <div class="action-buttons">
            <button class="bouton" @click.prevent="testConnection">{{ currentType.msg.testConnection }}</button>
            <button v-if="connected" class="bouton" @click.prevent="showFTPContent">{{ currentType.msg.showFTPContent }}</button>
        </div>
        <table v-if="files.length" class="uk-table uk-table-small uk-table-striped uk-table-middle">
            <thead>
                <tr>
                    <th>{{ currentType.msg.rights }}</th>
                    <th>{{ currentType.msg.size }}</th>
                    <th>{{ currentType.msg.date }}</th>
                    <th>{{ currentType.msg.fileName }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(file,index) in files" :key="index">
                    <td>{{ file[0] }}</td>
                    <td>{{ file[4] }}</td>
                    <td>{{ file[5] }} {{ file[6] }} {{ file[7] }}</td>
                    <td>{{ file[8] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>


<script>
import settingsForm from '@importexport/common/settingsForm.vue'
export default {
    components: {
        settingsForm
    },
    props : {
        currentType : {
            'type' : Object
        },
        formValues : {
            'type' : Object
        },
        execution : {
            'type' : Boolean,
            default : function() {
                return false;
            }
        }
    },
    data : function() {
        return {
            connected : false,
            files : []
        }
    },
    methods : {
        testConnection : async function() {
            let test = await this.ws.post('Sources', 'callback/testFTPConnection', this.formValues);
            if(! test.error && test[0] == true) {
                this.connected = true;
                this.notif.info(this.currentType.msg.testConnectionSuccess);
            } else {
                this.connected = false;
                this.notif.error(this.currentType.msg.testConnectionFailed);
            }
        },
        showFTPContent : async function() {
            let files = await this.ws.post('Sources', 'callback/showFTPContent', this.formValues);
            this.files = [];
            if(! files.error && files[0] != false) {
                for(let file of files) {
                    let splitedFile = file.split(' ');
                    splitedFile = splitedFile.filter(el => el != '');
                    this.$set(this.files, this.files.length, splitedFile);
                }
            } else {
                this.notif.error(this.currentType.msg.showFTPContentFailed);
            }
        }
    }
}
</script>

<style scoped>
.action-buttons {
    margin-top : 15px;
    margin-bottom: 15px;
    display:flex;
    justify-content: space-between;
}
</style>