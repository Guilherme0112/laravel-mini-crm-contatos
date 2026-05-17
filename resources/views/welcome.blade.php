<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mini-CRM | Monitor de Scores</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>

        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-900 font-[Figtree] text-gray-100 min-h-screen">
        
        <div id="app" class="max-w-4xl mx-auto p-6 lg:p-8">
            
            <div class="flex justify-between items-center border-b border-gray-800 pb-6 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                        Monitor de Contatos em Tempo Real
                    </h1>
                    <p class="text-sm text-gray-400 mt-1">Escutando o canal do Soketi via WebSockets</p>
                </div>
                <div class="bg-gray-800 px-4 py-2 rounded-lg text-xs font-semibold tracking-wider text-gray-300 border border-gray-700">
                    STATUS: <span :class="isConnected ? 'text-green-400' : 'text-red-400'">@{{ isConnected ? 'CONECTADO' : 'DESCONECTADO' }}</span>
                </div>
            </div>

            <div class="fixed top-5 right-5 z-50 space-y-3 max-w-sm w-full">
                <div v-for="toast in toasts" :key="toast.id" 
                     class="bg-gray-800 border-l-4 border-green-500 p-4 rounded shadow-2xl border border-gray-700 transition-all duration-300 transform translate-x-0">
                    <div class="flex items-start">
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-semibold text-white">¡Score Processado!</p>
                            <p class="text-xs text-gray-400 mt-1">O contato <b>@{{ toast.name }}</b> atingiu a nota <b>@{{ toast.score }}</b>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-700 bg-gray-800/50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-white">Histórico de Eventos Recentes</h2>
                    <span class="bg-blue-900/50 text-blue-300 text-xs px-2.5 py-1 rounded-full font-medium border border-blue-800">
                        @{{ notifications.length }} eventos nesta sessão
                    </span>
                </div>

                <div v-if="notifications.length === 0" class="p-12 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-4 opacity-40">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="text-base font-medium">Nenhum evento disparado ainda.</p>
                    <p class="text-xs text-gray-600 mt-1">Rode o worker das filas e processe o score de um contato para ver a mágica.</p>
                </div>

                <ul v-else class="divide-y divide-gray-700">
                    <li v-for="(item, index) in notifications" :key="index" class="p-6 hover:bg-gray-700/30 transition duration-150">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-white">@{{ item.name }}</p>
                                <p class="text-xs text-gray-400">@{{ item.email }} • @{{ item.phone }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="getScoreBadgeClass(item.score)" class="text-xs font-bold px-3 py-1 rounded-full border">
                                    Score: @{{ item.score }}
                                </span>
                                <p class="text-[10px] text-gray-500 mt-1">Status: @{{ item.status }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

        <script type="module">
            const { createApp, ref, onMounted } = Vue;

            createApp({
                setup() {
                    const notifications = ref([]);
                    const toasts = ref([]);
                    const isConnected = ref(false);

                    const getScoreBadgeClass = (score) => {
                        if (score >= 70) return 'bg-green-950/50 text-green-400 border-green-800';
                        if (score >= 40) return 'bg-yellow-950/50 text-yellow-400 border-yellow-800';
                        return 'bg-red-950/50 text-red-400 border-red-800';
                    };

                    const triggerToast = (contact) => {
                        const id = Date.now();
                        toasts.value.push({ id, ...contact });
                        
                        setTimeout(() => {
                            toasts.value = toasts.value.filter(t => t.id !== id);
                        }, 4000);
                    };

                    onMounted(() => {
                        const echo = window.Echo;

                        echo.connector.pusher.connection.bind('connected', () => {
                            isConnected.value = true;
                        });
                        echo.connector.pusher.connection.bind('disconnected', () => {
                            isConnected.value = false;
                        });

                        echo.channel('contacts')
                            .listen('.ContactScoreProcessed', (contact) => {
                                notifications.value.unshift(contact);
                                triggerToast(contact);
                            });
                    });

                    return {
                        notifications,
                        toasts,
                        isConnected,
                        getScoreBadgeClass
                    };
                }
            }).mount('#app');
        </script>
    </body>
</html>