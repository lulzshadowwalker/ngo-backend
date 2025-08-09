#!/bin/bash

# NGO Backend API Documentation Generator
# This script regenerates the API documentation using Scribe

echo "🚀 Regenerating NGO Backend API Documentation..."
echo "================================================"

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the Laravel project root directory."
    exit 1
fi

# Check if Scribe is installed
if ! php artisan list | grep -q "scribe:generate"; then
    echo "❌ Error: Scribe is not installed. Please run 'composer require knuckleswtf/scribe' first."
    exit 1
fi

# Option to clear existing documentation
read -p "🗑️  Clear existing documentation first? (y/N): " clear_docs

if [[ $clear_docs =~ ^[Yy]$ ]]; then
    echo "🧹 Clearing existing documentation..."
    rm -rf .scribe
    echo "✅ Cleared .scribe directory"
fi

# Generate documentation
echo "📝 Generating API documentation..."

# Check if custom environment file exists
if [ -f ".env.docs" ]; then
    read -p "🔧 Use .env.docs environment file? (Y/n): " use_env_docs
    if [[ ! $use_env_docs =~ ^[Nn]$ ]]; then
        echo "🔧 Using .env.docs environment configuration..."
        php artisan scribe:generate --env=docs
    else
        php artisan scribe:generate
    fi
else
    php artisan scribe:generate
fi

# Check if generation was successful
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ API Documentation generated successfully!"
    echo ""
    echo "📖 Documentation available at:"
    echo "   - Web: http://localhost:8000/docs"
    echo "   - Postman: http://localhost:8000/docs.postman"
    echo "   - OpenAPI: http://localhost:8000/docs.openapi"
    echo ""
    echo "📁 Generated files:"
    echo "   - Blade views: resources/views/scribe/"
    echo "   - Assets: public/vendor/scribe/"
    echo "   - Postman collection: storage/app/private/scribe/collection.json"
    echo "   - OpenAPI spec: storage/app/private/scribe/openapi.yaml"
    echo ""
    
    # Offer to start the server
    if ! pgrep -f "php artisan serve" > /dev/null; then
        read -p "🌐 Start Laravel development server? (Y/n): " start_server
        if [[ ! $start_server =~ ^[Nn]$ ]]; then
            echo "🌐 Starting Laravel development server..."
            echo "📖 Documentation will be available at: http://localhost:8000/docs"
            php artisan serve
        fi
    else
        echo "🌐 Laravel development server is already running"
        echo "📖 Documentation available at: http://localhost:8000/docs"
    fi
else
    echo "❌ Error: Documentation generation failed!"
    echo "💡 Tips:"
    echo "   - Check that all controllers have proper annotations"
    echo "   - Ensure database is accessible if using response calls"
    echo "   - Run with --verbose for more details: php artisan scribe:generate --verbose"
    exit 1
fi
