# Subsbu 1.0.1

Плагин, позволяющий пользователям записываться на мероприятия, созданные в плагине myEventON.

## Требования

1. PHP 7.4 и выше

2. WordPress 5 и выше

3. Плагин MyEventON

## Как использовать

1. Посещаем настройки e-mail-уведомлений и указываем там то, что нужно.

2. Создаём в MyEventON новое мероприятие. В настройках мероприятия при создании обязательно указываем, чтобы при нажатии на карточку мероприятия открывалась страница мероприятия. Также, обязательно нужно указать корректно часовой пояс.

3. На страницу, где будет проходить запись на мероприятие, помещаем шорткод следующего вида: `[subsbu id="event-subscribe-button" event="123" class="my-class" style="margin: 0px auto;"]Записаться|||Вы записаны![/subsbu]`.

Атрибуты:

* id — ID элемента кнопки в DOM-дереве. Нужен для того, чтобы повесить вызов модального окна с формами на кнопку.

* event — ID поста мероприятия (можно посмотреть под названием мероприятий в соответствующем списке MyEventON).

* class и style — атрибуты, отвечающие за CSS-стили кнопки. Аналогичны соответствующим атрибутам в HTML.

Контент шорткода задаёт текст для кнопки, который будет отображаться на кнопке до и после записи на мероприятие.
