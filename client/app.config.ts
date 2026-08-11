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
                th: 'px-4 py-3 text-xs font-semibold tracking-wide text-muted border-b border-default text-left rtl:text-right [&:has([role=checkbox])]:pe-0',
                td: 'p-4 text-sm text-muted whitespace-normal [&:has([role=checkbox])]:pe-0'
            },
        },

        // ---- Premium minimal primitives ----
        button: {
            slots: {
                base: 'rounded-lg font-medium inline-flex items-center disabled:cursor-not-allowed aria-disabled:cursor-not-allowed disabled:opacity-75 aria-disabled:opacity-75 transition-colors duration-200'
            },
        },

        card: {
            slots: {
                root: 'rounded-xl overflow-hidden'
            },
        },

        input: {
            slots: {
                base: 'w-full rounded-lg border-0 placeholder:text-dimmed focus:outline-none disabled:cursor-not-allowed disabled:opacity-75 transition-colors'
            },
        },

        textarea: {
            slots: {
                base: 'w-full rounded-lg border-0 placeholder:text-dimmed focus:outline-none disabled:cursor-not-allowed disabled:opacity-75 transition-colors'
            },
        },

        select: {
            slots: {
                base: 'relative group rounded-lg inline-flex items-center focus:outline-none disabled:cursor-not-allowed disabled:opacity-75 transition-colors',
                content: 'max-h-60 w-(--reka-select-trigger-width) bg-default shadow-xl rounded-xl ring ring-default overflow-hidden data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-select-content-transform-origin) pointer-events-auto flex flex-col',
                item: 'group relative w-full flex items-center select-none outline-none before:absolute before:z-[-1] before:inset-px before:rounded-lg data-disabled:cursor-not-allowed data-disabled:opacity-75 text-default data-highlighted:not-data-disabled:text-highlighted data-highlighted:not-data-disabled:before:bg-elevated/50'
            },
        },

        selectMenu: {
            slots: {
                base: 'relative group rounded-lg inline-flex items-center focus:outline-none disabled:cursor-not-allowed disabled:opacity-75 transition-colors',
                content: 'max-h-60 w-(--reka-select-trigger-width) bg-default shadow-xl rounded-xl ring ring-default overflow-hidden data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-select-content-transform-origin) pointer-events-auto flex flex-col',
                item: 'group relative w-full flex items-center select-none outline-none before:absolute before:z-[-1] before:inset-px before:rounded-lg data-disabled:cursor-not-allowed data-disabled:opacity-75 text-default data-highlighted:not-data-disabled:text-highlighted data-highlighted:not-data-disabled:before:bg-elevated/50'
            },
        },

        inputMenu: {
            slots: {
                base: 'rounded-lg',
                content: 'max-h-60 w-(--reka-combobox-trigger-width) bg-default shadow-xl rounded-xl ring ring-default overflow-hidden data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-combobox-content-transform-origin) pointer-events-auto flex flex-col',
                item: 'group relative w-full flex items-center gap-1.5 p-1.5 text-sm select-none outline-none before:absolute before:z-[-1] before:inset-px before:rounded-lg data-disabled:cursor-not-allowed data-disabled:opacity-75 text-default data-highlighted:not-data-disabled:text-highlighted data-highlighted:not-data-disabled:before:bg-elevated/50'
            },
        },

        dropdownMenu: {
            slots: {
                content: 'min-w-32 bg-default shadow-xl rounded-xl ring ring-default overflow-hidden data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-dropdown-menu-content-transform-origin) flex flex-col',
                item: 'group relative w-full flex items-center select-none outline-none before:absolute before:z-[-1] before:inset-px before:rounded-lg data-disabled:cursor-not-allowed data-disabled:opacity-75'
            },
        },

        contextMenu: {
            slots: {
                content: 'min-w-32 bg-default shadow-xl rounded-xl ring ring-default overflow-hidden data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-context-menu-content-transform-origin) flex flex-col',
                item: 'group relative w-full flex items-center select-none outline-none before:absolute before:z-[-1] before:inset-px before:rounded-lg data-disabled:cursor-not-allowed data-disabled:opacity-75'
            },
        },

        modal: {
            slots: {
                overlay: 'fixed inset-0 bg-elevated/50 backdrop-blur-sm',
                content: 'fixed bg-default divide-y divide-default flex flex-col focus:outline-none',
            },
            variants: {
                fullscreen: {
                    false: {
                        content: 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100vw-2rem)] max-w-lg max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-4rem)] rounded-2xl shadow-xl ring ring-default overflow-hidden'
                    }
                }
            },
        },

        badge: {
            variants: {
                size: {
                    xs: { base: 'text-[8px]/3 px-1.5 py-0.5 gap-1 rounded-full' },
                    sm: { base: 'text-[10px]/3 px-2 py-0.5 gap-1 rounded-full' },
                    md: { base: 'text-xs px-2.5 py-1 gap-1 rounded-full' },
                    lg: { base: 'text-sm px-3 py-1 gap-1.5 rounded-full' },
                    xl: { base: 'text-base px-3.5 py-1.5 gap-1.5 rounded-full' },
                },
            },
        },

        toast: {
            slots: {
                root: 'relative group overflow-hidden bg-default shadow-xl ring ring-default rounded-xl p-4 flex gap-2.5 focus:outline-none'
            },
        },

        skeleton: {
            base: 'animate-pulse rounded-lg bg-elevated',
        },

        tooltip: {
            slots: {
                content: 'flex items-center gap-1 bg-default text-highlighted shadow-sm rounded-md ring ring-default h-6 px-2.5 py-1 text-xs select-none data-[state=delayed-open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-tooltip-content-transform-origin) pointer-events-auto'
            },
        },

        alert: {
            slots: {
                root: 'relative overflow-hidden w-full rounded-xl p-4 flex gap-2.5'
            },
        },

        popover: {
            slots: {
                content: 'bg-default shadow-xl rounded-xl ring ring-default data-[state=open]:animate-[scale-in_100ms_ease-out] data-[state=closed]:animate-[scale-out_100ms_ease-in] origin-(--reka-popover-content-transform-origin) focus:outline-none pointer-events-auto'
            },
        },
    }
})
