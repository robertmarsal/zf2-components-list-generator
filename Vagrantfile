Vagrant.configure("2") do |config|
    config.vm.box      = "ubuntu/trusty64"
    config.vm.box_url  = "https://atlas.hashicorp.com/ubuntu/boxes/trusty64/versions/14.04/providers/virtualbox.box"
    config.vm.network "forwarded_port", guest: 80, host: 8081

    config.vm.provider "virtualbox" do |vb|
        vb.memory = "1024"
    end

    config.vm.provision :shell, inline: <<-SHELL
        apt-get update
        apt-get install -y php5 php5-xdebug git
        curl -Ss https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
    SHELL
end
