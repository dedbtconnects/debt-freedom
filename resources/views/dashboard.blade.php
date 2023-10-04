@push('scripts')
    <script src="https://cdn.spinwheel.io/dropin/v1/dim-initialize.js"></script>
    <script>
        const $spinwheelToken = '{{ $spinwheelToken }}';
        const $extUserId = '{{ $extUserId }}';
        let userId = null;
        let userName = null;

        function getUser() {
            postData = {
                userId: userId
            };

            axios.get('/spinwheel/get-user', {
                params: postData,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                const data = response.data;

                const prettyJson = JSON.stringify(data, null, 4);

                // Populate the div with the pretty-printed JSON
                document.getElementById('output').textContent = prettyJson;
            })
            .catch(error => {
                console.error('Error user data', error);
            });
        }

        function downloadPdf() {
            const downloadingAlert = document.getElementById('downloading-alert');
            downloadingAlert.classList.remove('invisible');
            downloadingAlert.classList.add('visible');

            postData = {
                userId: userId
            };

            axios.get('/spinwheel/liability-pdf', {
                params: postData,
                responseType: 'blob',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                // Create an object URL for the blob
                const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
                const link = document.createElement('a');
                const filename = userName + '-report.pdf';

                link.href = url;
                link.setAttribute('download', filename);  // Name your file
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                downloadingAlert.classList.remove('bg-green-400');
                downloadingAlert.classList.add('bg-blue-400');
                downloadingAlert.innerHTML = 'Download complete';
            })
            .catch(error => {
                downloadingAlert.classList.remove('bg-green-400');
                downloadingAlert.classList.add('bg-red-400');
                downloadingAlert.innerHTML = 'Error downloading PDF';

                console.error('Error downloading the PDF', error);
            });
        }

        window.onload = function() {
            const handler = Spinwheel.create({
                containerId: 'dim-container',
                onSuccess: (metadata) => {
                },
                onLoad: () => {
                },
                onExit: (metadata) => {
                },
                onEvent: (metadata) => {
                    switch (metadata.eventName) {
                        case "IDENTITY_CONNECTED":
                            let postData = {
                                extUserId: $extUserId
                            };

                            axios.post('/spinwheel/refresh-user', postData, {
                                headers: {
                                    'Content-Type': 'application/json',
                                    // Include CSRF token for Laravel
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => {
                                console.log(response.data); // Process the received data as needed

                                const data = response.data;
                                userId = data.data.userId;
                                userName = data.data.profile.firstName + '-' + data.data.profile.lastName;

                                getUser();
                                downloadPdf();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                        break;
                    }
                    console.log({metadata})
                },
                onError: (metadata) => {},
                onResize: (metadata) => {},
                dropinConfig: {
                    'module': 'identity-connect', // Change this if you'd like to use a different module
                    'token': $spinwheelToken
                }
            });


            handler.open() // to render dropin module
            //handler.exit() // to close dropin module
        }
    </script>
@endpush

<x-app-layout>
    <div class="flex">
        <div id="left-container">
            <div id="dim-container" class="w-[400px] h-[600px]"></div>
            <div id="downloading-alert" class="bg-green-400 p-6 invisible text-center">
                Downloading PDF
            </div>
        </div>
        <div id="right-pane" class="m-8">
            <pre id="output"></pre>
        </div>

    </div>
</x-app-layout>
