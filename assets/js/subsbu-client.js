const SubsbuClient = {
    buffer: null,
    subscribe: async (buttonId, event, user, success_text, key) => {
        const button = document.getElementById(buttonId);

        button.setAttribute('disabled', 'true');
        button.innerHTML = 'Подождите';

        const data = new FormData;

        data.append('subsbu-client-event', event);
        data.append('subsbu-client-user', user);
        data.append('subsbu-client-key', key);

        const request = await fetch(
            '/wp-json/subsbu/v1/subscribe',
            {
                method: 'POST',
                credentials: 'include',
                body: data
            }
        );
        
        if (request.ok)
        {
            const answer = await request.json();

            if (answer.code == 0)
            {
                button.innerHTML = success_text;

                console.log('SubsbuClient.subscribe(): success.');
            }
            else
            {
                button.innerHTML = 'Ошибка запроса';

                console.error(
                    'SubsbuClient.subscribe(): '+
                        answer.code+', "'+answer.message+'"'
                );
            }
        }
        else
        {
            button.innerHTML = 'Попробуйте ещё раз';
            
            if (button.hasAttribute('disabled')) button.
                removeAttribute('disabled');

            console.error('SubsbuClient.subscribe(): connection error.');
        }

    },
    flip: (donorId, recipientId) => {
        if (donorId == '' ||
            recipientId == '') return;

        SubsbuClient.fadeAppear(
            recipientId,
            100,
            -1,
            (recipientId, donorId) => {
                const recipient = document.getElementById(recipientId);

                SubsbuClient.buffer = recipient.innerHTML;

                recipient.innerHTML = document.getElementById(donorId).innerHTML;

                SubsbuClient.fadeAppear(recipientId, 0);
            }, donorId
        );
    },
    flop: (donorId, recipientId) => {
        SubsbuClient.fadeAppear(
            recipientId,
            100,
            -1,
            (recipientId, donorId) => {
                document.getElementById(recipientId).innerHTML = SubsbuClient.buffer;

                SubsbuClient.buffer = null;

                SubsbuClient.fadeAppear(recipientId, 0);
            }
        );
    },
    fadeAppear: (recipientId, opacity, mode = 1, callback = undefined, donorId = undefined) => {
        document.getElementById(recipientId).setAttribute('style', 'opacity: '+opacity+'%;');

        const goal = mode >= 0 ? 100 : 0;

        if (mode == 0) mode = -1;

        if (opacity == goal)
        {
            if (callback != undefined) callback(recipientId, donorId);
        }
        else setTimeout(
            SubsbuClient.fadeAppear,
            1,
            recipientId,
            (opacity + mode),
            mode,
            callback, donorId);
    }

};
