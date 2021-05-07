const SubsbuClient = {
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

                console.warn(
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

    }
};
