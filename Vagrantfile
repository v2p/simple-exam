# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.56.101"
  config.vm.synced_folder "./", "/vagrant_data", type: "nfs"

  config.vm.provider "virtualbox" do |vb|
    vb.name = "exam-v2p_main_machine"
    vb.customize ["modifyvm", :id, "--memory", "128"]
  end

  config.vm.provision "ansible" do |ansible|
    ansible.playbook = "provisioning/site.yml"
  end
end
