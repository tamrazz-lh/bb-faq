import { Placeholder, TextControl, Button } from '@wordpress/components';
import styles from './assets/edit.module.scss';

export default ({ attributes, setAttributes, metadata, focusOnLast, setFocusOnLast }) => {
    const EMPTY_ITEM = { question: '', answer: '' };

    const setItemData = (index, data) => {
        const item = attributes.items[index];
        const newItem = { ...item, ...data };
        attributes.items.splice(index, 1, newItem);
        setAttributes({ items: [...attributes.items] });
    };

    const addItem = (newItemData = {}) => {
        const newItem = { ...EMPTY_ITEM, ...newItemData };
        const newItems = [...attributes.items, newItem];
        setAttributes({ items: newItems });
        setFocusOnLast(newItem.answer ? 'answer' : 'question');
    };

    const deleteItem = (index) => {
        const updatedItems = [...attributes.items];
        updatedItems.splice(index, 1);
        setAttributes({ items: updatedItems });
    };

    const items = attributes.items.map((item, index, original) => {
        return (
            <>
                <TextControl
                    label="Вопрос"
                    value={item.question}
                    className={styles.question}
                    onChange={(val) => setItemData(index, { question: val })}
                    autoFocus={focusOnLast === 'question' && index === original.length - 1}
                />
                <TextControl
                    label="Ответ"
                    value={item.answer}
                    className={styles.answer}
                    onChange={(val) => setItemData(index, { answer: val })}
                    autoFocus={focusOnLast === 'answer' && index === original.length - 1}
                />
                <Button text="Удалить" isDestructive variant="link" className={styles.delete} onClick={() => deleteItem(index)} />
            </>
        );
    });

    return (
        <Placeholder icon={metadata.icon} label={metadata.title} className={styles.placeholder}>
            <div className={styles.wrapper}>
                {items}
                {items.length < 50 && (
                    <>
                        <TextControl label="Добавить вопрос" onChange={(val) => addItem({ question: val })} value="" placeholder="Вопрос..." className={styles.newQuestion} />
                        <TextControl label="Добавить ответ" onChange={(val) => addItem({ answer: val })} value="" placeholder="Ответ..." className={styles.newAnswer} />
                        <Button text="Добавить" isPrimary onClick={() => addItem()} />
                    </>
                )}
            </div>
        </Placeholder>
    );
};
