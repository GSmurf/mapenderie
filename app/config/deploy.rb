# Application settings
set :application,             "Siplec"
set :domain,                  "domain.com"

set :app_path,                "app"
set :web_path,                "web"

set :repository,              "git@frdesymfa1.gcd.net:/home/git/siplec.git"
set :scm,                     :git

set :keep_releases,           3
set :use_sudo,                false

# Be more or less verbose by commenting/uncommenting the following lines
#logger.level = Logger::IMPORTANT
#logger.level = Logger::INFO
#logger.level = Logger::DEBUG
#logger.level = Logger::TRACE
logger.level = Logger::MAX_LEVEL


# Upload parameters file corresponding to environment stage
after "deploy:setup" do
    run "if [ ! -d #{deploy_to}/shared/app/config ]; then mkdir -p #{deploy_to}/shared/app/config; fi"

    upload(
        '%s/parameters_%s.yml' % [File.dirname(__FILE__), fetch(:stage)],
        '%s/shared/app/config/parameters.yml' % fetch(:deploy_to)
    )
end

# Configure production instance
desc "Deployement de l'environnement de production"
task :production do
    set :stage,                "production"
    set :branch,               "master"

    set :deploy_to,            "/path/to/your/web/directory"

    role :app,                 "user@domain.com", :promary => true
    role :web,                 "user@domain.com"
end

desc "Deployement de l'environnement de recette"
task :recette do
    set :stage,                "recette"
    set :branch,               "master"

    set :deploy_to,            "/path/to/your/web/directory"

    role :app,                 "user@domain.com", :promary => true
    role :web,                 "user@domain.com"
end
