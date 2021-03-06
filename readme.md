# Roger-skyline-1  
### Синопсис  
* Хост (машина, на которой vbox): LMDE (Debian 10);  
* Гость (машина, к которой подключаемся через vbox): Debian 10.
### Подготовка
Устанавлиаем VirtualBox.  
Устанавливаем гостевую машину, используя любой дистрибутив Linux.
У меня это debian-10.6.0-amd64-netinst.iso размером 8 GB (прописываем вручную 8 миллиардов
байт), с двумя точками монтирования / (4.5 GB, берем с запасом, чтобы не заморачиваться)
и /home (оставшееся место), без desktop, обязательно с галочкой напротив ssh-manager
(можно ещё web-server, но его можно поставить и позже).  
После установки убедитесь в размерности диска и разделов:
```shell script
#from guest
fdisk -l --bytes
#result
Disk /dev/sda: 7.5 GiB, 8000000000 bytes, 15625000 sectors
Disk model: VBOX HARDDISK   
Units: sectors of 1 * 512 = 512 bytes
Sector size (logical/physical): 512 bytes / 512 bytes
I/O size (minimum/optimal): 512 bytes / 512 bytes
Disklabel type: dos
Disk identifier: 0x9a89ef74

Device     Boot   Start      End Sectors       Size Id Type
/dev/sda1  *       2048  9764863 9762816 4998561792 83 Linux
/dev/sda2       9766910 15624191 5857282 2998928384  5 Extended
/dev/sda5       9766912 15624191 5857280 2998927360 83 Linux
```
В настройках машины в VirtualBox выбираем пункт Network, включаем адаптер Bridge. Выбор этого
типа адаптера позволит использовать как Интернет, так и ssh. Ознакомьтесь с [гайдом](http://rus-linux.net/MyLDP/vm/VirtualBox-networking.html)
и сделайте выводы.  
Запускаем гостевую систему и входим под юзером root.  
Устанавливаем sudo и vim:  
```shell script
#from guest root
apt-get install sudo
apt-get install vim
```
Добавляем созданного в ходе установки пользователя (не root) в список пользователей, которым
разрешено использовать команду sudo:  
`sed -ie '/^# Allow members/i username ALL=(ALL:ALL) ALL' /etc/sudoers`  
Данная команда позволяет добавить пользователя username в файл sudoers, она
эквивалентна команде `vim /etc/sudoers` и внесению пользователя в список вручную.
### Настройка сети
Настало время выполнить первый пункт задания:
установить статический IP и маску подсети /30. Это довольно непростой пункт,
потому что конфигурация может различаться для разных устройств. Тут нет
универсального способа, к сожалению, поэтому придётся подумать.  
`ip -a`  
С помощью этой команды на гостевой машине выводим список сетевых устройств.
Скорее всего вы увидите два устройства: lo и enp0s3.
Нам нужно то, которое не lo, в моем случае это именно enp0s3.  
Посмотрите содержимое файла /etc/network/interfaces:  
`cat /etc/network/interfaces `  
Необходимо весь блок с информацией об enp0s3 привести к виду:  
```
# The primary network interface  
auto enp0s3  
allow-hotplug enp0s3  
iface enp0s3 inet static  
	address 192.168.1.X  
	netmask 255.255.255.252  
	gateway 192.168.1.1
```
Откуда взять данные?  
* **_Address_** -- для выяснения статического IP нам потребуется адрес роутера. Мой
192.168.1.1. Вообще полезно изучить сайт роутера. Из него мы можем узнать диапазон
адресов DHCP, то есть тех, которые роутер распределяет между устройствами сам.  
В адресной строке вбиваем адрес роутера, заходим в настройки LAN, ищем там DHCP.
Например, у меня это 192.168.1.64 - 192.168.1.253.  
Очевидно, если гостевая машина подключится к конкретному IP до того, как роутер
самостоятельно распределит этот IP, то всё будет исправно работать. IP прорисуется
в карте, как зарезервированный, его никому больше не отдадут.  
Но ведь может возникнуть обратная ситуация -- адрес будет распределен, после чего
гостевая машина попробует подключиться к нему же. Кажется, стоит иметь это в виду.  
Я пробовала как 192.168.1.1 (вне диапазона), так и 192.168.1.66 (в диапазоне).
В обоих случаях подключение работало, но в случае с 1.1 дополнительно перед подключением
по ssh приходилось с хоста выполнять команду `ping 192.168.1.1`, и лишь после этого
подключение имело успех.  
Да, кстати, если сеть настроена правильно, то команда ping должна передавать пакеты.
Любой другой ответ означает ошибку настройки сети. Возможно, натолкнёт на [размышления](http://jodies.de/ipcalc?host=192.168.1.1&mask1=24&mask2=30).
* **_Netmask_** /30 = 255.255.255.252 по заданию.  
* **_Gateway_** -- шлюз, пишем сюда адрес роутера.  
Без этих трёх компонентов ничего работать не будет (правильно).  
BTW, конкретно в файле `/etc/network/interfaces` можно оставить только строку `auto enp0s3`,
а всё остальное убрать в созданный вручную файл `/etc/network/interfaces.d/enp0s3`. Строго
говоря, лучше поступить именно так, потому что все приложения, работающие с подобной логикой,
могут рано или поздно обновиться, переписав по умолчанию стандартные файлы конфигураций (в данном
случае это interfaces).  
Делаем `reboot`, чтобы изменения вступили в силу. Делать `service networking restart` чуть менее, чем бесполезно.  
Если всё сконфигурировано верно, то на данном этапе у нас должны получаться две вещи:  
    *   ```shell script
        #from guest
        ping google.com
        ```
    *   ```shell script
        #from host  
        ssh username@192.168.1.X
        ```
Небольшая подсказка. Для всех команд вам не нужно будет использовать sudo, если вы
из гостевой машины работаете под root. Во всех остальных ситуациях - и от другого пользователя,
и через ssh от root, -- нужно использовать sudo, иначе половина команд не будет работать:
`-bash: service: command not found.`
### Редактируем ssh  
Если у нас на хосте ещё нет ssh-ключей, самое время сгенерировать новую пару:  
```shell script
#from host
ssh-keygen -t rsa
```
Публичный ключ будет лежать в папке `~/.ssh/`, если вы не выбрали другую во время генерации.
Теперь нужно передать публичный ключ гостевой системе, это пригодится в дальнейшем.  
```shell script
#from host
ssh-copy-id -i ~/.ssh/id_rsa.pub username@host
```
В моем случае это `ssh-copy-id -i ~/.ssh/id_rsa.pub barrett@192.168.1.66`.  
Этот ключ будет сохранен на гостевой машине по адресу `/home/username/.ssh/authorized_keys`.  
Отредактируем порт ssh-подключения. Согласно IANA целесообразнее всего будет использовать
порты от 49152 до 65535. Я возьму 50012. В файле `/etc/ssh/sshd_config` на гостевой
 машине заменим `#Port 22` на `Port 50012`.
Теперь подключиться к гостю можно только с прямым указанием порта:  
`ssh username@host -p portnumber`  
На этом редактирование `sshd_config` не закончено. Давайте зададим `PubkeyAuthentication yes`.
Теперь по ssh могут подключаться только те, чьи пары ключей совпадают. Но есть один
нюанс, если скопировать папку .ssh, которая на текущий момент лежит в /home/ нашего
юзера, в /root/, то есть дать пользователю root тот же ключ, то мы будем авторизованы.
Ранее этого не происходило, требовался пароль, но даже с ним войти под root было нельзя.  
Давайте проверим это.
```shell script
#from guest
sudo cp -r /home/username/.ssh/ /root/
#from host
ssh root@host -p 50012
```
Немедленно ликвидируем эту дыру! Задаем `PermitRootLogin no`.  
Финальный список изменений:  
```shell script
Port 50012                              #new ssh port
HostKey /etc/ssh/ssh_host_rsa_key       #адреса ssh ключей
HostKey /etc/ssh/ssh_host_ecdsa_key     #адреса ssh ключей
HostKey /etc/ssh/ssh_host_ed25519_key   #адреса ssh ключей
PermitRootLogin no                      #запрет на подключение под root
PubkeyAuthentication yes                #включить авторизацию по ключам
AuthorizedKeysFile                      #раскоменнтировать
PasswordAuthentication no               #выключить авторизацию по паролю
ChallengeResponseAuthentication no      #вообще выключить пароли
UsePAM no                               #вообще-вообще выключить пароли
AuthenticationMethods publickey         #добавить эту строку после UsePAM no
PrintMotd no                            #убрать непонятный текст после подключения по ssh
PrintLastLog yes                        #показывает дату последнего подключения
TCPKeepAlive no                         #не относится к заданию, используется вместе со строчками ниже
ClientAliveInterval 20                  #каждые 20 сек посылает клиенту запрос
ClientAliveCountMax 3                   #3 раза, чтобы получить ответ, иначе рвёт соединение
```
Не забываем после каждого изменения перезапускать sshd: `sudo service sshd restart`.
### Настройка FIREWALL  
За настройку firewall отвечает стандарный инструмент iptables. Но мы будем использовать
утилиту [ufw](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-18-04-ru) для упрощения работы.
```shell script
#from guest
sudo apt-get install ufw
sudo ufw status
sudo ufw enable
```
Необходимо разрешить доступ только сервисам извне. Таких всего 3: ssh, http и https.  
```shell script
sudo ufw default deny incoming          #запретить входящие
sudo ufw default allow outgoing         #разрешить исходящие
sudo ufw allow http                     #разрешить http
sudo ufw allow https                    #разрешить https
sudo ufw allow %portnumber%/tcp         #разрешить ssh-порт, который мы изменили, по протоколу tcp
sudo ufw reload                         #применить изменения
```
Если ещё опция limit, она ограничивает доступ по ip юзера, который стучится в порт более 6 раз
в течение 30 секунд. Мне кажется, использовать limit для http и https - довольно неоднозначная идея,
а для защиты ssh мы всё равно будем использовать fail2ban.  
### Защита от DOS  
#### База и защита ssh
Для защиты установим приложение fail2ban. Оно позволяет по логам из /var/log/ или любой другой
 папки, которую мы укажем, временно банить нарушителей через iptables. Можно ограничиться лишь iptables,
но интерфейс fail2ban проще.
```shell script
#from guest
sudo apt-get install fail2ban
sudo fail2ban status
sudo fail2ban enable
```
Базовых настроек fail2ban в принципе хватает, но как минимум нужно указать наш
нестандартный порт ssh-подключения и изменить пару строк, чтобы было удобно тестировать.  
Все настройки fail2ban находятся в `/etc/fail2ban/`. Файл `jail.conf` трогать не надо,
это базовая конфигурация защиты. Для локальных изменений создадим файл `jail.local`,
он имеет более высокий приоритет обработки.  
В `/etc/fail2ban/` есть три папки: action.d, jail.d и filter.d, в каждой из них лежат конфигурации,
отвечающие за конкретный пункт "тюремного блока". 
Файлы конфигураций должны иметь разрешение `.conf`, кроме одного файла - `jail.local`. "Тюрьмы"
можно хранить как в папке `jail.d `отдельно друг от друга (1 конфиг = 1 файл), так и
все вместе в `jail.local`. Это очень похоже на `/etc/network/interfaces` и `/etc/network/interfaces.d/`.  
Изменим конфигурацию по умолчанию:  
```shell script
#from guest
#/etc/fail2ban/jail.local
[DEFAULT]
ignoreip = 192.168.1.67/30              #игнорировать подсеть/ip (белый лист)
maxretry = 3                            #количество попыток входа/нежелательного действия
banaction = ufw                         #какое действие применить
bantime = 600                           #по умолчанию в секундах
findtime = 3600                         #по умолчанию 600, интервал времени, в течение которого должно произойти matretry нежелательных действий для бана
```
Здесь 192.168.1.67/30 - это адрес широковещательной сети, указанный в `ip a` гостя.
Данная настройка есть в jail.conf, но лучше переназначить её в jail.local, чётко контролируя, какие
ip не будут подвергаться фильтрации.  
Теперь создадим конфигурацию конкретно для ssh:
```shell script
#from guest
#/etc/fail2ban/jail.d/sshd.conf
[sshd]                                  #произвольное название, либо одно из стандартных, тогда оно перезапишется
enabled = true                          #включено/выключено (false/true)
port = 50012                            #номер порта
filter = sshd                           #какой применить фильтр
logpath = /var/log/auth.log             #какой лог смотреть
```
**_Filter_** берется из папки `filter.d`, в данном примере он ищет файл `.conf`, в имени которого
есть sshd. Сам фильтр представляет собой регулярное выражение, на наличие которого
сканируется файл из **_logpath_**, и если найдено больше совпадений, чем _**maxretry**_, ip
нарушителя банится методом **_banaction_** на _**bantime**_. Ну и раз уж мы поставили ufw,
то, удостоверившись, что в папке action.d есть файл ufw.conf, назначим его палачом. По
умолчанию палачом является iptables-multiport.  
Логично спросить, если в конфигурации [sshd] мы не указали никаких banaction, bantime и
прочих вещей, откуда им взяться? Они будут взяты из [DEFAULT]. Если нужны другие,
прописываем их в этом блоке вручную.   
С настройками выше мы забаним пользователя, совершившего более 3 неудачных попыток подключения
по ssh, на 10 минут. Как проверить, всё ли работает?  
```shell script
#/etc/fail2ban/jail.local
[DEFAULT]
ignoreip = your_guest_ip                #или оставить поле пустым, тогда в списке исключений не будет никого
#from guest
sudo service fail2ban restart
#from host repeat 4 times
ssh not_existed_user@host -p 50012      #подключаемся с несуществующего пользователя 4 раза
```
4 раза -- больше, чем наш maxretry, результатом выполнения команды должно стать:  
`ssh: connect to host 192.168.1.66 port 50012: Connection refused`  
Тем временем fail2ban забанил наш хост, в чём можно убедиться самим:  
```shell script
#from guest
sudo cat /var/log/fail2ban.log | grep Ban
#result
2020-11-09 19:29:02,678 fail2ban.actions        [613]: NOTICE  [sshd] Ban 192.168.1.65
```
Где 192.168.1.65 -- это ip хоста. Через 10 минут бан будет снят. И не забывайте после
изменения настроек делать service fail2ban restart!  
#### Защита http  
Теперь установим веб-сервер. Если вы ставили дистрибутив Linux с галочкой установить
web-сервер, то он уже есть, если нет, то скачиваем:
```shell script
#from guest
sudo apt-get install apache2
sudo service apache2 status
```  
Если всё правильно, если при настройке ufw вы разрешили 80/tcp, то, вбив в адресной строке
браузера 192.168.1.66 (ip гостевой машины), вы увидите что-то типа Apache2 Debian Default Page. При этом
команда `nmap 192.168.1.66`, выполненная с хоста, которая ранее показывала, что http порт закрыт,
теперь покажет, что он открыт.
Стартовая страница лежит в `/var/www/html/index.html`. Изменения в этом файле отобразятся
при просмотре в браузере.
Теперь создадим правило для защиты от атак по http-порту либо в папке jail.d, либо
в файле jail.local:  
```shell script
#from guest
#/etc/fail2ban/jail.d/http-apache-protect.conf
[http-apache-protect]                    #ваше название
enabled = true                           #включить/выключить
port = http,https                        #список портов
filter = http-apache-protect             #файл из папки filter.d
logpath = /var/log/apache*/access.log    #файл, куда apache пишет лог
maxretry = 100                           #количество максимальных попыток
```
Затем создаем фильтр:
```shell script
#from guest
#/etc/fail2ban/filter.d/http-apache-protect.conf
[Definition]
failregex = %regex%                      #вместо %regex% - регулярное выражение
ignoreregex = 
```
Тут я не стала приводить конкретный пример, чтобы вы самостоятельно изучили регулярные
выражения. Подсказка: вам нужно изучить файл /var/log/apache2/access.log после
обращения к сайту через браузер хоста. Проанализировав эти данные, вы сможете понять,
какое происходит действие, где расположен ip визитёра, а google.com поможет вам
его извлечь.  
Облегчить работу поможет сайт [Проверка регулярных выражений](https://planetcalc.ru/708/),
а также встроенный инструмент fail2ban-regex. Чтобы им воспользоваться, создайте
фильтр `/etc/fail2ban/filter.d/filtername.conf`, после чего выполните `sudo fail2ban-regex /var/log/apache2/access.log /etc/fail2ban/filter.d/filtername.conf`.
Первый аргумент - это лог, который мы проверяем, второй файл - фильтр, т.е. регулярное выражение,
которое мы применяем к логу.
```shell script
#from guest
sudo fail2ban-regex /var/log/apache2/access.log /etc/fail2ban/filter.d/filtername.conf
#bad result (your regex does not work):
Lines: 2726 lines, 0 ignored, 0 matched, 2726 missed
#good result (your regex works):
Lines: 2726 lines, 0 ignored, 2714 matched, 12 missed
```
Конечно, количество совпадений не гарантирует правильный результат, но само их наличие
означает, что вы на верном пути. Не забываем `sudo service fail2ban restart`!
Настало время атаковать сервер с помощью [Slowloris](https://github.com/gkbrk/slowloris) ([видео](https://www.youtube.com/watch?v=F7nk7LUQ5bw&t=113s&ab_channel=TheCodeby)). Клонируем
репозиторий на хост. Для наглядности установим на гостевую машину tcpdump:  
```shell script
#from guest
sudo apt-get install tcpdump            #для визуализации Slowloris
sudo tcpdump | grep 192.168.1.65        #лог запросов от хоста (подставить свой) в реальном времени
```
Теперь в терминале хоста начинаем атаку!  
```shell script
#from host ~/Slowloris/
python slowloris.py 192.168.1.66        #подставить ip гостевой машины
```
В терминале гостя с открытым tcpdump начнётся настоящий праздник, правда, длиться
он будет ровно 100 пакетов, после чего хост попадёт в бан (если его нет ignoreip,
об этом было выше). Чтобы убедиться в этом, посмотрим логи:  
```shell script
sudo cat /var/log/fail2ban.log | grep Ban
#result:
2020-11-10 00:14:40,820 fail2ban.actions        [1559]: NOTICE  [http-apache-protect] Ban 192.168.1.65
#or
sudo fail2ban-client status your-jail-rule-name
#result:
Banned IP list:   192.168.1.65
#undo ban:
sudo fail2ban-client set http-apache-protect unbanip 192.168.1.65
```
Или зайдем на сайт сервера -- сейчас нас туда не пустит, т.к. мы в бане. Slowloris отбит!
## Защита от сканирования портов
```shell script
#from guest
sudo apt-get install portsentry -y
#/etc/default/portsentry
TCP_MODE="atcp"                        #использовать режим advanced
UDP_MODE="audp"                        #использовать режим advanced
#/etc/portsentry/portsentry.conf
BLOCK_UDP="1"                          #заблокировать сканирующего
BLOCK_TCP="1"                          #заблокировать сканирующего
```
Использование atcp и audp обусловлено [мануалом](https://www.opennet.ru/docs/RUS/portsentry/portsentry4.html):  
>Advanced Stealth Scan Detection
Этот режим используется для проверки всех портов в промежутке от 1 до
ADVANCED_PORT_TCP (для TCP) или ADVANCED_PORT_UDP (для UDP). Порты, открытые
другими программами и перечисленные в ADVANCED_EXLUDE_TCP(для TCP) или
ADVANCED_EXCLUDE_UDP(для UDP) исключаются из проверки. Любой хост, попытавшийся
подключится к порту в этом промежутке, тут же блокируется. Самый удобный для
использования метод, т.к. реакция на сканирование или подключение у этого метода
самая быстрая, а также Portsentry в этом режиме использует меньше процессорного
времени, чем в других. Задается опциями командной строки: -atcp - для TCP-портов
и -audp - для UDP-портов.
>
Теперь можно сканировать порты:  
```shell script
#from host (192.168.1.66 is guest)
nmap -v 192.168.1.66                            #IP гостя
nmap -v -Pn -p 0-2000,60000 192.168.1.66
```
И если всё работает правильно, то:  
```shell script
#from guest
cat /etc/hosts.deny
#result:
ALL: 192.162.1.65                               #IP хоста
```
Данную строку необходимо убрать из файла, иначе больше по ssh вы не подключитесь, и перезагрузить гостя.  
## Отключение неиспользуемых сервисов
Некоторые системные утилиты можно найти [тут](https://www.freedesktop.org/software/systemd/man/systemd.special.html).
```shell script
#from guest
#systemctl list-unit-files | grep enabled
apache2.service                        enabled          #apache web-server
apparmor.service                       enabled          #менеджер профилей Линукса, можно сносить
autovt@.service                        enabled          #что-то про запуск виртуальных терминалов, лучше оставить
console-setup.service                  enabled          #настройки клавиатуры, шрифтов и т.д. - отключаем
cron.service                           enabled          #планировщик событий
dbus-org.freedesktop.timesync1.service enabled          #помогает сервисам внутри системы общаться друг с другом. можно снести
fail2ban.service                       enabled          #оставить, это наш фаерволл
getty@.service                         enabled          #нужно для логина, оставляем
keyboard-setup.service                 enabled          #не удалось понять, зачем это, но вроде всё осталось по-прежнему
networking.service                     enabled          #сеть, оставляем
rsyslog.service                        enabled          #системные логи, оставляем
ssh.service                            enabled          #ssh подключение, оставляем
sshd.service                           enabled          #аналогично оставляем
syslog.service                         enabled          #оставляем
systemd-fsck-root.service              enabled-runtime  #часть системного пакета, не выключается
systemd-timesyncd.service              enabled          #часть системного пакета утилит
ufw.service                            enabled          #наш фаервол, оставляем
remote-fs.target                       enabled          #нечто вроде порядка загрузки, позволяет загружаться с других устройств
apt-daily-upgrade.timer                enabled          #можно выключить
apt-daily.timer                        enabled          #можно выключить
logrotate.timer                        enabled          #не стала отключать, делает копии логов, чтобы случайно не удалить текущий
man-db.timer                           enabled          #часть db, мы его выключили, этот тоже выключаем
```
## Планировщик cron  
### Скрипт обновления
Скрипт в общем и целом должен выглядеть как `apt-get update && apt-get upgrade -y`
с вашими опциальными дополнениями. Расположить его можно в `/etc/cron.d/`:
```shell script
#from guest ROOT:
sudo touch /etc/cron.d/update_script.sh
sudo chmod og-wrx /etc/cron.d/update_script.sh          #отобрать все права у всех, кроме владельца
sudo chmod u+wrx /etc/cron.d/update_script.sh           #добавить все права владельцу, т.е. root
grep PATH /etc/crontable                                #скопируйте результат
crontable -e                                            #вставьте его сюда, этот файл может быть не в курсе переменных окружения
#add:
0 4 * * 1 /etc/cron.d/update_script.sh > /dev/null      # > /dev/null чтобы не отправлял отчёт на внутреннюю почту
@reboot /etc/cron.d/update_script.sh > dev/null
#to see all tasks for user:
crontab -l
```
Можно, конечно, добавить те же правила в файл `/etc/crontable`, но к нему имеют доступ
все, а вот к `crontable -e` извне добраться сложнее, тем более следующий пункт задания -
отслеживать изменения в `/etc/crontable`, - нам тонко на это намекает.  
### Скрипт отслеживания изменений  
```shell script
#from guest ROOT:
sudo apt-get install sendmail -y                         #сервис по отправке писем
sudo touch /etc/cron.d/cron_scan.sh
sudo chmod og-wrx /etc/cron.d/cron_scan.sh
sudo chmod u+wrx /etc/cron.d/cron_scan.sh
#script:
#!/bin/bash
DIFF=$(diff /etc/crontab.bak /etc/crontab)
cat /etc/crontab > /etc/crontab.bak
if [ "$DIFF" != "" ]
then
	echo "Crontab was changed. Sending e-mail to root."
	echo -e "CRONTAB WAS MODIFIED!!!\n$DIFF" | sendmail root
else
	echo "Crontab is unchanged."
fi
################################################################################
crontab -e
#add
0 0   * * * /etc/cron.d/cron_scan.sh > /dev/null
```
## Web-part
SSL можно установить по [этому гайду](https://help.ubuntu.ru/wiki/apache_%D0%B8_https).  
При посещении вашего сайта http будет заменено на https, и вам скорее всего скажут,
что сертификат не настоящий. Это действительно так, и в Google Chrome можно нажать
сверху слева на Not secure, чтобы увидеть ваш самодельный сертификат с полями, которые
вы заполняли в ходе установки.  
Команда nmap с хоста теперь покажет, что порт 443 (https) открыт. Это нормально в общем-то,
потому что какие-то порты должны быть открыты, ведь на них установлены серверы.  
Для данейшей настройки я воспользовалась [этим гайдом](https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-18-04-ru).  
Ну а вас впереди ожидает увлекательный творческий процесс по адресу `/var/www/`.