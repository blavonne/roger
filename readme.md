# Roger-skyline-1  
### Синопсис  
* Хост (машина, на которой vbox): LMDE (Debian 10);  
* Гость (машина, к которой подключаемся через vbox): Debian 10.
### Подготовка
Устанавлиаем VirtualBox.  
Устанавливаем гостевую машину, используя любой дистрибутив Linux.
У меня это debian-10.6.0-amd64-netinst.iso размером 8 ГБ, с двумя точками монтирования
/ (4.2 GB) и /home (оставшееся место), без desktop, обязательно с галочкой напротив
ssh-manager.  
В настройках машины выбираем пункт Network, включаем адаптер Bridge. Выбор этого
типа адаптера позволит использовать как Интернет, так и ssh.  
Подключаемся к гостевой системе через root.  
Устанавливаем sudo и vim:  
```shell script
#from guest root
apt-get install sudo
apt-get install vim
```
Добавляем созданного пользователя (не root) в список пользователей, которым
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
Скорее всего вы увидите два устройства: lo и enp0s3
. Нам нужно то, которое не lo, в моем случае это именно enp0s3.  
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
### Защита от DOS  
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
Конфигурация fail2ban находится в `/etc/fail2ban/`. Файл `jail.conf` трогать не надо,
это базовая конфигурация защиты. Для локальных изменений создадим файл `jail.local`,
он имеет более высокий приоритет обработки.  
Далее есть три папки: action.d, jail.d и filter.d, в каждой из них лежат конфигурации,
отвечающие за конкретный пункт "тюремного блока". "Тюремный блок", как правило,
выглядит как:  
```shell script
#/etc/fail2ban/jail.d/sshd.conf
[sshd]                                  #произвольное название, либо одно из стандартных, тогда оно перезапишется
enabled = true                          #включено/выключено (false/true)
port = 50012                            #номер порта
filter = sshd                           #какой применить фильтр
logpath = /var/log/auth.log             #какой лог смотреть
maxretry = 3                            #количество попыток входа/нежелательного действия
banaction = ufw                         #какое действие применить
bantime = 600                           #по умолчанию в секундах
findtime = 3600                         #по умолчанию 600, интервал времени, в течение которого должно произойти matretry нежелательных действий для бана
```
**_Filter_** берется из папки `filter.d`, в данном примере он ищет файл `.conf`, в имени которого
есть sshd. Сам фильтр представляет собой регулярное выражение, на наличие которого
сканируется файл из **_logpath_**, и если найдено больше совпадений, чем _**maxretry**_, ip
нарушителя банится методом **_banaction_** на _**bantime**_.  
Файлы конфигураций должны иметь разрешение `.conf`, кроме одного файла - `jail.local`. "Тюрьмы"
можно хранить как в папке `jail.d `отдельно друг от друга (1 конфиг = 1 файл), так и
все вместе в `jail.local`. Это очень похоже на `/etc/network/interfaces` и `/etc/network/interfaces.d/`.  
Ещё понадобится изменить конфигурацию по умолчанию.  
```shell script
#/etc/fail2ban/jail.local
[DEFAULT]
ignoreip = 192.168.1.67/30              #игнорировать подсеть/ip
```
Здесь 192.168.1.67/30 - это адрес широковещательной сети, указанный в `ip a` гостя.
Данная настройка есть в jail.conf, но лучше переназначить её в jail.local, чётко контролируя, какие
ip не будут подвергаться фильтрации. Как проверить, всё ли работает?  
```shell script
#/etc/fail2ban/jail.local
[DEFAULT]
ignoreip = your_guest_ip
#from guest
sudo service fail2ban restart
#from host repeat 4 times
ssh not_existed_user@host -p 50012       #подключаемся с несуществующего пользователя 4 раза
```
4 раза -- больше, чем наш maxretry, результатом выполнения команды должно стать:  
`ssh: connect to host 192.168.1.66 port 50012: Connection refused`  
Тем временем fail2ban забанил наш хост, в чём можно убедиться самим:  
```shell script
#from guest
sudo tail /var/log/fail2ban.log
#result
2020-11-09 19:29:02,678 fail2ban.actions        [613]: NOTICE  [sshd] Ban 192.168.1.65
```
Где 192.168.1.65 -- это ip хоста. Через 10 минут бан будет снят. И не забывайте после
изменения настроек делать service fail2ban restart!  

