export default defineAppConfig({
    icon: {
        cssLayer: 'base',
    },
    ui: {
        colors: {
            primary: 'blue',
            secondary: 'blue',
            success: 'green',
            error: 'red',
            warning: 'amber',
            info: 'blue',
            neutral: 'neutral',
            form: 'form'
        },
        
        tabs: {
            slots: {
                root: 'space-y-0',
                list: 'h-auto',
                trigger: 'h-[30px]'
            }
        },

        keyboard: {
            defaultVariant: 'subtle',
        },

        icons: {
            arrowLeft: 'i-lucide-arrow-left',
            arrowRight: 'i-lucide-arrow-right', 
            check: 'i-lucide-check',
            chevronDoubleLeft: 'i-lucide-chevrons-left',
            chevronDoubleRight: 'i-lucide-chevrons-right',
            chevronDown: 'i-lucide-chevron-down',
            chevronLeft: 'i-lucide-chevron-left',
            chevronRight: 'i-lucide-chevron-right',
            chevronUp: 'i-lucide-chevron-up',
            close: 'i-lucide-x',
            ellipsis: 'i-lucide-ellipsis',
            external: 'i-lucide-arrow-up-right',
            folder: 'i-lucide-folder',
            folderOpen: 'i-lucide-folder-open',
            loading: 'i-lucide-refresh-cw',
            minus: 'i-lucide-minus',
            plus: 'i-lucide-plus',
            search: 'i-lucide-search'
        },

        table: {
            slots: {
                td: 'p-4 text-sm text-muted whitespace-normal [&:has([role=checkbox])]:pe-0'
            },
        }
    }
})
