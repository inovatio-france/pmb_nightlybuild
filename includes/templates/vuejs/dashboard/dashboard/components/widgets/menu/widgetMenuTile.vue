<template>
    <div class="widget-menus-tile">
        <div 
            v-for="menu in widget.widgetSettings.menu" 
            class="widget-menu-tile"
            :style="`background-color: ${menu.backgroundColor}`"
            @click="openTab(menu.link)">
    
            <label :style="`color: ${helper.calculateTextColor(menu.backgroundColor)};`">
                {{ menu.label }}
            </label>
        </div>

        <div 
            v-for="menu in widget.widgetSettings.menuCustom" 
            class="widget-menu-tile"
            :style="`background-color: ${menu.backgroundColor}`"
            @click="openTab(menu.link)">
    
            <label :style="`color: ${helper.calculateTextColor(menu.backgroundColor)};`">
                {{ menu.label }}
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        props: ["widget", "widget_type"],
        methods: {
            openTab: function (link) {
                window.open(link, "_blank");
            }
        }
    }
</script>